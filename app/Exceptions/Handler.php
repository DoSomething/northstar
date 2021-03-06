<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    const PRODUCTION_ERROR_MESSAGE = 'Looks like something went wrong. We\'ve noted the problem and will try to get it fixed!';

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        OAuthServerException::class,
        ModelNotFoundException::class,
        NorthstarValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        // If we throw a 422 Validation Exception, write something to the log for later review:
        if (
            $e instanceof ValidationException ||
            $e instanceof NorthstarValidationException
        ) {
            info('Validation failed.', [
                'url' => request()->path(),
                'errors' => $e->errors(),
            ]);

            return;
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        // If we receive a OAuth exception, get the included PSR-7 response,
        // convert it to a standard Symfony HttpFoundation response and return.
        if ($e instanceof OAuthServerException) {
            $psrResponse = $e->generateHttpResponse(
                app(ResponseInterface::class),
            );

            return (new HttpFoundationFactory())->createResponse($psrResponse);
        }
        // If Intervention can't parse a file (corrupted or wrong type), return 422.
        // @TODO: Handle this with a validation rule on our v3 routes.
        if ($e instanceof \Intervention\Image\Exception\NotReadableException) {
            abort(422, 'Invalid image provided.');
        }

        // Re-cast specific exceptions or uniquely render them:
        if ($e instanceof HttpException && $e->getStatusCode() === 429) {
            return $this->rateLimited($e);
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif (
            $e instanceof ValidationException ||
            $e instanceof NorthstarValidationException
        ) {
            return $this->invalidated($request, $e);
        } elseif ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException('That resource could not be found.');
        } elseif ($e instanceof AuthorizationException) {
            $e = new AccessDeniedHttpException($e->getMessage(), $e);
        }

        // If request has 'Accepts: application/json' header or we're on a route that
        // is in the `api` middleware group, render the exception as JSON object.
        if (
            $request->ajax() ||
            $request->wantsJson() ||
            has_middleware('api')
        ) {
            return $this->buildJsonResponse($e);
        }

        // Redirect to root if trying to access disabled methods on a controller or access denied to user.
        if (
            $e instanceof MethodNotAllowedHttpException ||
            $e instanceof AccessDeniedHttpException
        ) {
            return redirect('/');
        }

        return parent::render($request, $e);
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Illuminate\Http\Response|JsonResponse
     */
    protected function rateLimited($exception)
    {
        event(new \App\Events\Throttled());

        $retryAfter = $exception->getHeaders()['Retry-After'];
        $minutes = ceil($retryAfter / 60);
        $pluralizedNoun = $minutes === 1 ? 'minute' : 'minutes';
        $message =
            'Too many attempts. Please try again in ' .
            $minutes .
            ' ' .
            $pluralizedNoun .
            '.';

        if (request()->wantsJson() || request()->ajax()) {
            return new JsonResponse($message, 429, $exception->getHeaders());
        }

        return redirect()
            ->back()
            ->with('flash', $message);
    }

    /**
     * Convert an validation exception into flash redirect or JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @param ValidationException|NorthstarValidationException $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function invalidated($request, $e)
    {
        $wantsJson = $request->ajax() || $request->wantsJson();

        // TODO: We use a custom response format for validation errors that used to
        // be a part of Northstar, our identity API. We should standardize these.
        if ($wantsJson && $e instanceof NorthstarValidationException) {
            return $e->getResponse();
        }

        if ($wantsJson) {
            return $this->invalidJson($request, $e);
        }

        return redirect()
            ->back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->withErrors($e->errors());
    }

    /**
     * Convert an authentication exception into an redirect or JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $e)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->buildJsonResponse(
                new HttpException(401, 'Unauthorized.'),
            );
        }

        return redirect()->guest('register');
    }

    /**
     * Build a JSON error response for API clients.
     *
     * @param Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildJsonResponse(Exception $e)
    {
        $code = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $shouldHideErrorDetails = $code == 500 && !config('app.debug');
        $response = [
            'error' => [
                'code' => $code,
                'message' => $shouldHideErrorDetails
                    ? self::PRODUCTION_ERROR_MESSAGE
                    : $e->getMessage(),
            ],
        ];

        // Show more information if we're in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return response()->json($response, $code);
    }

    /**
     * Get the default context variables for logging exceptions.
     *
     * @return array
     */
    protected function context()
    {
        // We handle adding context in ContextFormatter, and specifically
        // want to disable Laravel's default behavior of appending email here.
        return [];
    }
}

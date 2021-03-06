<?php

namespace App\Http\Controllers\Web;

use App\Auth\PasswordRules;
use App\Auth\Registrar;
use App\Events\PasswordUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends BaseController
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new UserController, inject dependencies and
     * set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('auth:web');
        $this->middleware('role:admin,staff', ['only' => ['show']]);
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('users.show', [
            'user' => auth()
                ->guard('web')
                ->user(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // @TODO: Implement this route.
        return redirect('/');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (
            !$user->can('editProfile', [
                auth()
                    ->guard('web')
                    ->user(),
                $user,
            ])
        ) {
            throw new AccessDeniedHttpException();
        }

        $defaultCountry = country_code() ?: 'US';

        return view('users.edit', compact('user', 'defaultCountry'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (
            !$user->can('editProfile', [
                auth()
                    ->guard('web')
                    ->user(),
                $user,
            ])
        ) {
            throw new AccessDeniedHttpException();
        }

        $this->registrar->validate($request, $user, [
            'first_name' => 'required|max:50',
            'last_name' => 'nullable|max:50',
            'birthdate' => 'nullable|required|date',
            'sms_status' => 'required_with_all:mobile',
        ]);

        $values = $request->all();

        if (!$values['mobile']) {
            // HACK: Clear any existing SMS status values if we don't have the user's mobile number.
            // This should likely happen in UserObserver -- but that logic is pretty complex.
            $values['sms_status'] = null;
        }

        $user->fill($values)->save();

        return redirect('/');
    }
}

<?php

use Illuminate\Support\Facades\Route;

/*
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
 *
 * @see \App\Providers\RouteServiceProvider
 */

// Homepage
Route::get('/', 'UserController@home');

// Users
Route::resource('users', 'UserController', [
    'except' => ['index', 'create', 'delete'],
]);

// Authorization flow for the Auth Code OAuth grant.
Route::get('authorize', 'AuthController@getAuthorize');
Route::get('callback', 'AuthController@getCallback');

// Login & Logout
Route::get('login', 'AuthController@getLogin');
Route::post('login', 'AuthController@postLogin');
Route::get('logout', 'AuthController@getLogout');

// Two-Factor Authentication
Route::get('totp', 'TotpController@prompt');
Route::post('totp', 'TotpController@verify');
Route::get('totp/configure', 'TotpController@configure');
Route::post('totp/configure', 'TotpController@store');

// Facebook Continue
Route::get('facebook/continue', 'FacebookController@redirectToProvider');
Route::get('facebook/verify', 'FacebookController@handleProviderCallback');

// Google Continue
Route::get('google/continue', 'GoogleController@redirectToProvider');
Route::get('google/verify', 'GoogleController@handleProviderCallback');

// Registration
Route::get('register', 'AuthController@getRegister');
Route::post('register', 'AuthController@postRegister');

// Profile
Route::get('profile/about', 'ProfileAboutController@edit');
Route::patch('profile/about', 'ProfileAboutController@update');
Route::get('profile/subscriptions', 'ProfileSubscriptionsController@edit');
Route::patch('profile/subscriptions', 'ProfileSubscriptionsController@update');

// Change Password
Route::patch('users/{id}/password', 'PasswordController@update')->name(
    'passwords.update',
);

// Password Reset
Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{type}/{token}', [
    'as' => 'password.reset',
    'uses' => 'ResetPasswordController@showResetForm',
]);
Route::post('password/reset/{type}', 'ResetPasswordController@reset');

// Originals
Route::get('originals/{post}', 'OriginalsController@show');

// Administration
if (config('features.admin')) {
    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            // Homepage
            Route::view('/', 'admin.home');

            // Actions
            Route::resource('/actions', 'Admin\ActionsController', [
                'except' => ['index', 'show'],
            ]);

            // Campaigns
            Route::get(
                '/campaigns/{id}/actions/create',
                'Admin\ActionsController@create',
            );

            Route::resource('/campaigns', 'Admin\CampaignsController', [
                'except' => ['index', 'show'],
            ]);

            // Clubs
            Route::resource('/clubs', 'Admin\ClubsController', [
                'except' => ['index', 'show', 'destroy'],
            ]);

            // FAQ
            Route::view('/faq', 'admin.pages.faq');

            // Groups
            Route::resource('groups', 'Admin\GroupsController', [
                'except' => ['create', 'index', 'show'],
            ]);

            // Group Types
            Route::resource('group-types', 'Admin\GroupTypesController', [
                'except' => ['index', 'show'],
            ]);

            Route::get(
                'group-types/{id}/groups/create',
                'Admin\GroupsController@create',
            );

            // Users
            Route::resource('users', 'Admin\UserController', [
                'except' => ['create', 'store'],
            ]);

            Route::post('users/{user}/resets', [
                'as' => 'users.resets.create',
                'uses' => 'Admin\UserController@sendPasswordReset',
            ]);

            Route::delete('users/{user}/promotions', [
                'as' => 'users.promotions.destroy',
                'uses' => 'Admin\PromotionsController@destroy',
            ]);

            // Fastly Redirects
            Route::resource('redirects', 'Admin\RedirectsController');
        });

    // Client-side Admin routes:
    Route::prefix('admin')
        ->middleware('auth:web', 'role:staff,admin')
        ->group(function () {
            // Actions
            Route::view('/actions/{id}', 'admin.app');

            // Actions
            Route::view('/action-stats', 'admin.app');

            // Campaigns
            Route::view('/campaigns', 'admin.app');
            Route::view('/campaigns/{id}', 'admin.app');
            Route::view('campaigns/{id}/{status}', 'admin.app');

            // Clubs
            Route::view('/clubs', 'admin.app');
            Route::view('/clubs/{id}', 'admin.app');

            // Groups
            Route::view('groups', 'admin.app');
            Route::view('groups/{id}', 'admin.app');
            Route::view('groups/{id}/posts', 'admin.app');

            // Schools
            Route::view('schools/{id}', 'admin.app')->name('schools.show');
        });
}

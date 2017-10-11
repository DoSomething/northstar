<?php

/**
 * Define all of our model factories. Model factories give
 * you a convenient way to create models for testing and seeding your
 * database. Just tell the factory how a default model should look.
 *
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Northstar\Models\User::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->optional(0.5)->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'mobilecommons_id' => $faker->randomNumber(5),
        'sms_status' => $faker->randomElement(['active', 'undeliverable']),
        'facebook_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'addr_street1' => $faker->streetAddress,
        'city' => $faker->city,
        'addr_state' => $faker->stateAbbr,
        'addr_zip' => $faker->postcode,
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
    ];
});

$factory->defineAs(Northstar\Models\User::class, 'staff', function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'facebook_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'role' => 'staff',
    ];
});

$factory->defineAs(Northstar\Models\User::class, 'admin', function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'facebook_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'role' => 'admin',
    ];
});

$factory->defineAs(\Northstar\Models\Client::class, 'authorization_code', function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'authorization_code',
        'scope' => ['user', 'openid', 'profile', 'role:staff', 'role:admin'],
        'redirect_uri' => $faker->url,
    ];
});

$factory->defineAs(\Northstar\Models\Client::class, 'password', function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'password',
        'scope' => ['user', 'profile', 'role:staff', 'role:admin'],
    ];
});

$factory->defineAs(\Northstar\Models\Client::class, 'client_credentials', function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->numerify('phpunit-###'),
        'title' => $faker->company,
        'description' => $faker->sentence,
        'allowed_grant' => 'client_credentials',
        'scope' => ['user', 'admin'],
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Reaction;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Reaction::class, function (Faker $faker) {
    $faker->addProvider(new FakerNorthstarId($faker));

    return [
        'northstar_id' => factory(User::class)->create(),
        'post_id' => $faker->randomNumber(7),
    ];
});

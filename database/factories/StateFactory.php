<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\State;
use Faker\Generator as Faker;

$factory->define(State::class, function (Faker $faker) {
    $country = App\Country::pluck('id')->toArray();
    return [
        'state_name' => $faker->unique()->city,
        'state_description' => $faker->citySuffix,
        'country_id' => $faker->randomElement($country),
    ];
});

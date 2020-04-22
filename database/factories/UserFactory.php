<?php

use Illuminate\Support\Str;
use App\User;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'first_name'           => $faker->name,
        'last_name'            => $faker->name,
        'display_name'         => $faker->name,
        'email'                => $faker->unique()->safeEmail,
        'username'             => $faker->unique()->safeEmail,
        'password'             => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token'       => Str::random(10),
        'auto_attach_programs' => 0,
        'address'              => $faker->streetAddress,
        'address2'             => $faker->address,
        'city'                 => $faker->city,
        'state'                => '',
        'zip'                  => $faker->postcode,
        'status'               => 'Active',
        'access_disabled'      => 0,

    ];
});

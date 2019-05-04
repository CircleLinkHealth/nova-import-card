<?php

use App\Patient;
use App\User;
use Faker\Generator as Faker;

$factory->define(Patient::class, function (Faker $faker) {
    return [
        'user_id'  => function () {
            return factory(User::class)->create()->id;
        },
        'birth_date'   => $faker->date('y-m-d'),
    ];
});

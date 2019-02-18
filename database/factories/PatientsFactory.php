<?php

use App\Patient;
use Faker\Generator as Faker;

$factory->define(Patient::class, function (Faker $faker) {
    return [
        'user_id'  => function () {
            return factory(App\User::class)->create()->id;
        },
        'birth_date'   => $faker->date('y-m-d'),
    ];
});

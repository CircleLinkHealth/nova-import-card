<?php

use Faker\Generator as Faker;

$factory->define(App\AwvUser::class, function (Faker $faker) {
    return [
        'cpm_user_id'  => function () {
            return factory(App\User::class)->create()->id;
        },
        'birth_date'   => $faker->date('y-m-d'),
        'number'       => $faker->phoneNumber,
    ];
});

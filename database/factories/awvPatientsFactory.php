<?php

use Faker\Generator as Faker;

$factory->define(App\awvPatients::class, function (Faker $faker) {
    return [
        'cpm_user_id' =>  function () {
            return factory(App\User::class)->create()->id;
        },

        'number' => $faker->phoneNumber,
    ];
});

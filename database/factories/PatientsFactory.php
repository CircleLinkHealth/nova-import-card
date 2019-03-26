<?php

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Generator as Faker;

$factory->define(Patient::class, function (Faker $faker) {
    return [
        'user_id'  => function () {
            return factory(User::class)->create()->id;
        },
        'birth_date'   => $faker->date('y-m-d'),
    ];
});

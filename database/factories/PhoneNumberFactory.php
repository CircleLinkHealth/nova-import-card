<?php

use Faker\Generator as Faker;
use CircleLinkHealth\Customer\Entities\User;

$factory->define(App\PhoneNumber::class, function (Faker $faker) {
    return [
        'user_id'  => function () {
            return factory(User::class)->create()->id;
        },
        'number' => $faker->phoneNumber,
    ];
});

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\User;
use Faker\Generator as Faker;

$factory->define(CircleLinkHealth\Customer\Entities\PhoneNumber::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'number' => $faker->phoneNumber,
    ];
});

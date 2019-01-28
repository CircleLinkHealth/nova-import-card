<?php

use Faker\Generator as Faker;

$factory->define(App\InvitationLink::class, function (Faker $faker) {
    return [
        'patient_user_id' => $faker->numberBetween(0, 40),
        'patient_name'    => $faker->name,
        'birth_date'      => $faker->date('y-m-d'),
        'survey_id'       => $faker->randomNumber(),
        'link_token'      => $faker->url,
        'is_expired'      => $faker->boolean(),
    ];
});

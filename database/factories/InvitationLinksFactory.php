<?php

use App\InvitationLink;
use Faker\Generator as Faker;

$factory->define(InvitationLink::class, function (Faker $faker) {
    return [
        'patient_info_id'     => function () {
            return factory(App\Patient::class)->create()->id;
        },
        'survey_id'           => $this->faker->randomNumber('8'),
        'link_token'          => $this->faker->url,
        'is_manually_expired' => false,
    ];
});

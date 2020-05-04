<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\Location;

$factory->define(Location::class, function (Faker\Generator $faker) {
    return [
        'is_primary'     => true,
        'name'           => $faker->company,
        'phone'          => formatPhoneNumberE164($faker->phoneNumber),
        'fax'            => formatPhoneNumberE164($faker->phoneNumber),
        'address_line_1' => $faker->streetAddress,
        'city'           => $faker->city,
        'state'          => 'NY',
        'postal_code'    => 12345,
    ];
});

<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

use CircleLinkHealth\Customer\Entities\Practice;

$factory->define(Practice::class, function (Faker\Generator $faker) {
    $name = $faker->company;

    return [
        'name'           => $name,
        'display_name'   => $name,
        'active'         => true,
        'federal_tax_id' => $faker->randomNumber(5),
        //        'user_id',
        //        'same_clinical_contact',
        'clh_pppm' => 0,
        //        'same_ehr_login',
        //        'sms_marketing_number',
        'weekly_report_recipients' => 'mantoniou@circlelinkhealth.com',
        'invoice_recipients'       => 'mantoniou@circlelinkhealth.com',
        'bill_to_name'             => $name,
        //        'auto_approve_careplans',
        'send_alerts'           => 1,
        'outgoing_phone_number' => $faker->phoneNumber,
        'term_days'             => 30,
    ];
});

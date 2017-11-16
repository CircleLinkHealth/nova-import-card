<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Enrollee;
use App\Entities\Invite;
use App\Nurse;
use App\Practice;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'display_name' => $faker->name,
        'first_name'   => $faker->firstName,
        'last_name'    => $faker->lastName,
        'email'        => $faker->safeEmail,
        'password'     => bcrypt(str_random(10)),
        'timezone'     => 'America/Chicago',
        'username'     => $faker->userName,
        'address'      => $faker->streetAddress,
        'city'         => $faker->city,
        'state'        => 'IL',
        'zip'          => '12345',
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmWeight::class, function (Faker\Generator $faker) {

    $starting = rand(300, 450);

    return [
        'monitor_changes_for_chf' => $faker->boolean(),
        //        'patient_id' => '', this has to be passed in when calling the factory
        'starting'                => $starting,
        'target'                  => $starting - rand(100, 150),
    ];
});


$factory->define(\App\Models\CPM\Biometrics\CpmBloodPressure::class, function (Faker\Generator $faker) {

    $systolicStarting  = rand(110, 140);
    $diastolicStarting = rand(60, 70);

    $systolicTarget  = $systolicStarting - rand(10, 20);
    $diastolicTarget = $diastolicStarting - rand(15, 20);

    return [
//        'patient_id' => '', this has to be passed in when calling the factory
'starting' => "$systolicStarting/$diastolicStarting",
'target'   => "$systolicTarget/$diastolicTarget",
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmBloodSugar::class, function (Faker\Generator $faker) {

    return [
//        'patient_id' => '', this has to be passed in when calling the factory
'starting'     => rand(140, 300),
'starting_a1c' => rand('6.7', '13.8'),
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmSmoking::class, function (Faker\Generator $faker) {

    return [
//        'patient_id' => '', this has to be passed in when calling the factory
'starting' => rand(15, 50),
'target'   => rand(0, 8),
    ];
});

$factory->define(\App\Models\CCD\CcdInsurancePolicy::class, function (Faker\Generator $faker) {

    $types = [
        'Medicare',
        'Medicaid',
    ];

    $relations = [
        'Self',
        'G8',
        'Next Of Kin',
    ];

    return [
//        'ccda_id' => '', this has to be passed in when calling the factory
//        'patient_id' => '', this has to be passed in when calling the factory
'name'       => $faker->company,
'type'       => $types[array_rand($types, 1)],
'policy_id'  => $faker->swiftBicNumber,
'relation'   => $relations[array_rand($relations, 1)],
'subscriber' => $faker->name,
'approved'   => rand(0, 1),
    ];
});

$factory->define(Invite::class, function (Faker\Generator $faker) {
    return [
        'inviter_id' => factory(App\User::class)->create()->id,
        'email'      => $faker->email,
        'subject'    => 'subject',
        'message'    => 'message',
        'code'       => generateRandomString(20),
    ];
});

$factory->define(Enrollee::class, function (Faker\Generator $faker) {
    return [
        'provider_id' => 2430,
        'practice_id' => 8,
        'mrn'         => $faker->randomNumber(6),
        'dob'         => $faker->date('Y-m-d'),

        'first_name' => $faker->firstName,
        'last_name'  => $faker->lastName,
        'address'    => $faker->streetAddress,
        'city'       => $faker->city,
        'state'      => 'NY',
        'zip'        => $faker->randomNumber(5),

        'lang' => 'EN',

        'primary_phone' => $faker->phoneNumber,
        'cell_phone'    => $faker->phoneNumber,
        'home_phone'    => $faker->phoneNumber,
        'other_phone'   => $faker->phoneNumber,

        'status' => Enrollee::TO_CALL,

        'primary_insurance'   => $faker->company,
        'secondary_insurance' => $faker->company,
        'tertiary_insurance'  => $faker->company,
        'has_copay'           => $faker->boolean(),

        'email'                   => $faker->email,
        'referring_provider_name' => 'Dr. Demo',
        'problems'                => 'Hypertension, High Cholesterol',
        'cpm_problem_1'           => 1,
        'cpm_problem_2'           => 2,
    ];
});

$factory->define(Nurse::class, function (Faker\Generator $faker) {
});

$factory->define(Practice::class, function (Faker\Generator $faker) {
    $name = $faker->company;

    return [
        'name'                     => $name,
        'display_name'             => $name,
        'active'                   => true,
        'federal_tax_id'           => $faker->randomNumber(5),
        //        'user_id',
        //        'same_clinical_contact',
        'clh_pppm'                 => 0,
        //        'same_ehr_login',
        //        'sms_marketing_number',
        'weekly_report_recipients' => 'mantoniou@circlelinkhealth.com',
        'invoice_recipients'       => 'mantoniou@circlelinkhealth.com',
        'bill_to_name'             => $name,
        //        'auto_approve_careplans',
        //        'send_alerts',
        'outgoing_phone_number'    => $faker->phoneNumber,
        'term_days'                => 30,
    ];
});

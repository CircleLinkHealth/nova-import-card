<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Invite;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Practice;

$factory->define(
    \CircleLinkHealth\Customer\Entities\User::class,
    function (Faker\Generator $faker) {
        return [
            'display_name' => $faker->name,
            'first_name'   => $faker->firstName,
            'last_name'    => $faker->lastName,
            'email'        => $faker->safeEmail,
            'password'     => bcrypt('secret'),
            'timezone'     => 'America/Chicago',
            'username'     => $faker->userName,
            'address'      => $faker->streetAddress,
            'city'         => $faker->city,
            'state'        => 'IL',
            'zip'          => '12345',
        ];
    }
);

$factory->define(
    \CircleLinkHealth\TimeTracking\Entities\Activity::class,
    function (Faker\Generator $faker) use ($factory) {
        return [
            'type'          => $faker->text(15),
            'duration'      => $faker->numberBetween(1, 120),
            'duration_unit' => 'seconds',
            'performed_at'  => Carbon::now(),
        ];
    }
);

$factory->define(App\Note::class, function (Faker\Generator $faker) use ($factory) {
    return [
        'patient_id'           => $factory->create(\CircleLinkHealth\Customer\Entities\User::class)->id,
        'author_id'            => $factory->create(\CircleLinkHealth\Customer\Entities\User::class)->id,
        'logger_id'            => $factory->create(\CircleLinkHealth\Customer\Entities\User::class)->id,
        'body'                 => $faker->text(100),
        'isTCM'                => $faker->boolean(50),
        'type'                 => $faker->text(10),
        'did_medication_recon' => $faker->boolean(50),
        'performed_at'         => Carbon::now(),
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmWeight::class, function (Faker\Generator $faker) {
    $starting = rand(300, 450);

    return [
        'monitor_changes_for_chf' => $faker->boolean(),
        //        'patient_id' => '', this has to be passed in when calling the factory
        'starting' => $starting,
        'target'   => $starting - rand(100, 150),
    ];
});

$factory->define(\App\Models\CPM\Biometrics\CpmBloodPressure::class, function (Faker\Generator $faker) {
    $systolicStarting = rand(110, 140);
    $diastolicStarting = rand(60, 70);

    $systolicTarget = $systolicStarting - rand(10, 20);
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
        'inviter_id' => factory(\CircleLinkHealth\Customer\Entities\User::class)->create()->id,
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

$factory->define(Location::class, function (Faker\Generator $faker) {
    $name = $faker->company;

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

$factory->define(EligibilityBatch::class, function (Faker\Generator $faker) {
    $practice = factory(Practice::class)->create();

    return [
        'practice_id' => $practice->id,
        'type'        => EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE,
        'options'     => [],
    ];
});

$factory->define(EligibilityJob::class, function (Faker\Generator $faker) {
    $batch = factory(EligibilityBatch::class)->create();

    return [
        'batch_id' => $batch->id,
        'data'     => [],
    ];
});

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Call;
use App\DirectMailMessage;
use App\Services\PdfReports\Handlers\AthenaApiPdfHandler;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Invite;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;

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

$factory->define(Patient::class, function (Faker\Generator $faker) use ($factory) {
    return [
        'user_id'    => $factory->create(\CircleLinkHealth\Customer\Entities\User::class)->id,
        'birth_date' => $faker->dateTimeBetween('-90 years', '-30 years'),
        'ccm_status' => $faker->randomElement([Patient::ENROLLED, Patient::PAUSED, Patient::WITHDRAWN]),
        'gender'     => $faker->randomElement(['M', 'F']),
        'mrn_number' => $faker->randomNumber(8),
    ];
});

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

$factory->define(
    \CircleLinkHealth\SharedModels\Entities\CpmWeight::class,
    function (Faker\Generator $faker) {
        $starting = rand(300, 450);

        return [
            'monitor_changes_for_chf' => $faker->boolean(),
            //        'patient_id' => '', this has to be passed in when calling the factory
            'starting' => $starting,
            'target'   => $starting - rand(100, 150),
        ];
    }
);

$factory->define(
    \CircleLinkHealth\SharedModels\Entities\CpmBloodPressure::class,
    function (Faker\Generator $faker) {
        $systolicStarting = rand(110, 140);
        $diastolicStarting = rand(60, 70);

        $systolicTarget = $systolicStarting - rand(10, 20);
        $diastolicTarget = $diastolicStarting - rand(15, 20);

        return [
            //        'patient_id' => '', this has to be passed in when calling the factory
            'starting' => "$systolicStarting/$diastolicStarting",
            'target'   => "$systolicTarget/$diastolicTarget",
        ];
    }
);

$factory->define(
    \CircleLinkHealth\SharedModels\Entities\CpmBloodSugar::class,
    function (Faker\Generator $faker) {
        return [
            //        'patient_id' => '', this has to be passed in when calling the factory
            'starting'     => rand(140, 300),
            'starting_a1c' => rand('6.7', '13.8'),
        ];
    }
);

$factory->define(
    \CircleLinkHealth\SharedModels\Entities\CpmSmoking::class,
    function (Faker\Generator $faker) {
        return [
            //        'patient_id' => '', this has to be passed in when calling the factory
            'starting' => rand(15, 50),
            'target'   => rand(0, 8),
        ];
    }
);

$factory->define(
    \CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy::class,
    function (Faker\Generator $faker) {
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
    }
);

$factory->define(Invite::class, function (Faker\Generator $faker) {
    return [
        'inviter_id' => factory(\CircleLinkHealth\Customer\Entities\User::class)->create()->id,
        'email'      => $faker->email,
        'subject'    => 'subject',
        'message'    => 'message',
        'code'       => generateRandomString(20),
    ];
});

$factory->define(Enrollee::class, function (Faker\Generator $faker) use ($factory) {
    if (isProductionEnv()) {
        $practice = Practice::whereName('demo')->firstOrFail();
        $provider = \CircleLinkHealth\Customer\Entities\User::ofType('provider')
            ->ofPractice($practice->id)
            ->firstOrFail();
    } else {
        $practice = Practice::where('name', 'demo')
            ->where('is_demo', true)
            ->first();

        if ( ! $practice) {
            $practice = factory(\CircleLinkHealth\Customer\Entities\Practice::class)->create([
                'name'    => 'demo',
                'is_demo' => true,
            ]);
        }

        $provider = \CircleLinkHealth\Customer\Entities\User::ofType('provider')
            ->first();

        if ( ! $provider) {
            // fixme: this will not work - need a user with role provider
            $provider = factory(\CircleLinkHealth\Customer\Entities\User::class)->create();
        }
    }

    $phones = collect([]);
    while ($phones->count() < 4) {
        $number = $faker->phoneNumber;
        if (validateUsPhoneNumber($number)) {
            $phones->push($number);
        }
    }

    $str = new \CircleLinkHealth\Core\StringManipulation();

    return [
        'provider_id' => $provider->id,
        'practice_id' => $practice->id,
        'mrn'         => $faker->randomNumber(6),
        'dob'         => $faker->date('Y-m-d', now()->subYears(18)),

        'first_name' => $faker->firstName,
        'last_name'  => $faker->lastName,
        'address'    => $faker->streetAddress,
        'city'       => $faker->city,
        'state'      => 'NY',
        'zip'        => $faker->randomNumber(5),

        'lang' => 'EN',

        'primary_phone' => $str->formatPhoneNumberE164($phones->random()),
        'cell_phone'    => $str->formatPhoneNumberE164($phones->random()),
        'home_phone'    => $str->formatPhoneNumberE164($phones->random()),
        'other_phone'   => $str->formatPhoneNumberE164($phones->random()),

        'status' => Enrollee::TO_CALL,

        'primary_insurance'   => $faker->company,
        'secondary_insurance' => $faker->company,
        'tertiary_insurance'  => $faker->company,
        'has_copay'           => $faker->boolean(),

        'email'                   => $faker->email,
        'referring_provider_name' => $provider->getFullName(),
    ];
});

$factory->define(Nurse::class, function (Faker\Generator $faker) {
});

$factory->define(Practice::class, function (Faker\Generator $faker) {
    $name = $faker->company;

    while (Practice::whereName($name)->exists()) {
        $name = $faker->company;
    }

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
    $practice = Practice::where('name', 'demo')
        ->where('is_demo', true)
        ->first();
    if ( ! $practice) {
        $practice = factory(\CircleLinkHealth\Customer\Entities\Practice::class)->create(['is_demo' => true]);
    }

    return [
        'practice_id' => $practice->id,
        'options'     => [],
        'type'        => 'test',
    ];
});

$factory->define(EligibilityJob::class, function (Faker\Generator $faker) {
    $batch = factory(EligibilityBatch::class)->create();

    return [
        'batch_id' => $batch->id,
        'data'     => [],
    ];
});

$factory->define(Ehr::class, function (Faker\Generator $faker) {
    return [
        'name'               => "EHR: $faker->company",
        'pdf_report_handler' => AthenaApiPdfHandler::class,
    ];
});

$factory->define(TargetPatient::class, function (Faker\Generator $faker) {
    $batch = factory(EligibilityBatch::class)->create();
    $ehr = factory(Ehr::class)->create();

    return [
        'batch_id'          => $batch->id,
        'practice_id'       => $batch->practice_id,
        'ehr_id'            => $ehr->id,
        'ehr_patient_id'    => $faker->numberBetween(1, 2),
        'ehr_practice_id'   => $faker->numberBetween(1, 5000),
        'ehr_department_id' => $faker->numberBetween(1, 10),
        'department_id'     => $faker->numberBetween(1, 10),
        'description'       => $faker->text,
    ];
});

$factory->define(Call::class, function (Faker\Generator $faker) {
    return [
        'type'     => $faker->randomElement(['call', 'task']),
        'sub_type' => $faker->randomElement([
            'Call Back',
            'CP Review',
            'Get Appt.',
            'Other Task',
            'Refill',
            'Send Info',
        ]),
        'inbound_cpm_id'  => null, // to be filled in during test
        'outbound_cpm_id' => null, // to be filled in during test
        'scheduled_date'  => $faker->date(),
        'window_start'    => '09:00',
        'window_end'      => '17:00',
        'attempt_note'    => $faker->text(30),
        'is_manual'       => $faker->boolean,
        'asap'            => $faker->boolean,
        'note_id'         => null,
        'is_cpm_outbound' => 1,
        'service'         => 'phone',
        'status'          => $faker->randomElement([Call::SCHEDULED, Call::REACHED, Call::DONE]),
        'scheduler'       => null, // to be filled in during test
    ];
});

$factory->define(DirectMailMessage::class, function (Faker\Generator $faker) {
    return [
        'message_id'      => $faker->uuid,
        'from'            => $faker->safeEmail,
        'to'              => $faker->safeEmail,
        'subject'         => $faker->title,
        'body'            => $faker->paragraph,
        'num_attachments' => 0,
        'error_text'      => null,
        'status'          => 'success',
        'direction'       => 'received',
    ];
});

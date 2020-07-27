<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Faker\Factory;

trait SetupTestCustomerTrait
{
    use UserHelpers;

    /**
     * @return \CircleLinkHealth\Customer\Entities\User
     */
    public function createAdmin(Practice $practice)
    {
        $roles = [
            Role::whereName('administrator')->first()->id,
        ];

        return $this->setupUser($practice->id, $roles);
    }

    /**
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createLocation(Practice $practice)
    {
        $faker = Factory::create();

        return Location::create([
            'practice_id' => $practice->id,
            'is_primary'  => 1,
            'name'        => $practice->name,
            'phone'       => $faker->phoneNumber,
            //                'fax',
            'address_line_1' => $faker->address,
            'address_line_2' => $faker->address,
            'city'           => $faker->city,
            //            'state' => $faker->state??,
            'timezone'    => $faker->timezone,
            'postal_code' => $faker->postcode,
            //                'ehr_login',
            //                'ehr_password',
        ]);
    }

    /**
     * @param mixed $providerId
     *
     * @return \CircleLinkHealth\Customer\Entities\User
     */
    public function createPatient(Practice $practice, $providerId)
    {
        $roles = [
            Role::byName('participant')->id,
        ];

        $patient = $this->setupUser($practice->id, $roles);
        $date    = Carbon::now();

        $attr = \factory(Patient::class)->raw();
        $patient->patientInfo->setCcmStatusAttribute($attr['ccm_status']);
        $patient->patientInfo->gender     = $attr['gender'];
        $patient->patientInfo->mrn_number = $attr['mrn_number'];
        $patient->patientInfo->birth_date = $attr['birth_date'];
        $patient->patientInfo->save();

        //attach problems, summaries, chargeable services
        $problems   = CpmProblem::get();
        $problemIds = $problems->pluck('id');
        $months     = collect([1, 2, 3, 4, 5, 6]);

        $patient->patientSummaries()->create([
            'month_year' => Carbon::now()->copy()->subMonth($months->random())->startOfMonth()->toDateString(),
            'ccm_time'   => 1400,
            'approved'   => 1,
        ]);

        $patient->chargeableServices()->attach(1);

        $problems
            ->random(5)
            ->each(function ($p) use ($patient) {
                $patient->ccdProblems()
                    ->create([
                        'is_monitored'   => true,
                        'name'           => $p->name,
                        'cpm_problem_id' => $p->id,
                    ]);
            });

        $patient->load('ccdProblems');

        //careplan
        $patient->carePlan()->updateOrCreate([
            'mode'                  => CarePlan::WEB,
            'care_plan_template_id' => 1,
        ], [
            'status'        => CarePlan::PROVIDER_APPROVED,
            'provider_date' => Carbon::now(),
        ]);

        //activities
        $activityDuration = collect([150, 275, 348, 567, 764, 895, 988, 1010, 1111, 1235, 1300]);
        $activityType     = collect([
            'CarePlanSetup',
            'ReviewProgress',
            'CareCoordination',
            'MedicationReconciliation',
            'Alerts Review',
        ]);
        $patient->activities()->createMany([
            [
                'provider_id'   => $providerId,
                'logger_id'     => 0,
                'type'          => $activityType->random(),
                'duration'      => $activityDuration->random(),
                'duration_unit' => 'seconds',
                'performed_at'  => $date->copy()->subDay(5)->toDateTimeString(),
            ],
            [
                'provider_id'   => $providerId,
                'logger_id'     => 0,
                'type'          => $activityType->random(),
                'duration'      => $activityDuration->random(),
                'duration_unit' => 'seconds',
                'performed_at'  => $date->copy()->subDay(7)->toDateTimeString(),
            ],
        ]);

        $patient->setBillingProviderId($providerId);

        $patient->load(
            'patientInfo',
            'activities',
            'patientSummaries',
            'cpmProblems',
            'chargeableServices',
            'carePlan',
            'billingProvider'
        );

        return $patient;
    }

    /**
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createPractice()
    {
        $faker = Factory::create();
        $name  = $faker->company;

        $practice = Practice::create([
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
            //        'send_alerts',
            'outgoing_phone_number' => $faker->phoneNumber,
            'term_days'             => 30,
        ]);

        $saas = SaasAccount::firstOrCreate([
            'name' => 'CircleLink Health',
            'slug' => 'circlelink-health',
        ]);

        $practice->saasAccount()->associate($saas);
        $practice->save();

        return $practice;
    }

    /**
     * @return \CircleLinkHealth\Customer\Entities\User
     */
    public function createProvider(Practice $practice)
    {
        $roles = [
            Role::whereName('provider')->first()->id,
        ];

        return $this->setupUser($practice->id, $roles);
    }

    /**
     * @param int $patientCount
     *
     * @return mixed
     */
    public function createTestCustomerData($patientCount = 100)
    {
        $practice = $this->createPractice();
        $location = $this->createLocation($practice);
        $provider = $this->createProvider($practice);
        $admin    = $this->createAdmin($practice);
        $patients = [];

        for ($x = $patientCount; $x > 0; --$x) {
            $patients[] = $this->createPatient($practice, $provider->id);
        }

        $patients = collect($patients);

        $data['practice'] = $practice;
        $data['location'] = $location;
        $data['patients'] = $patients;
        $data['provider'] = $provider;
        $data['admin']    = $admin;

        return $data;
    }
}

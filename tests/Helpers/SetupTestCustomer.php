<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 09/04/2018
 * Time: 8:07 PM
 */

namespace Tests\Helpers;

use App\CarePlan;
use App\Location;
use App\Models\CPM\CpmProblem;
use App\Patient;
use App\Practice;
use App\Role;
use Carbon\Carbon;
use Faker\Factory;


trait SetupTestCustomer
{
    use UserHelpers;


    /**
     * @param Practice $practice
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createLocation(Practice $practice)
    {

        $faker = Factory::create();

        $location = Location::create([
            'practice_id'    => $practice->id,
            'is_primary'     => 1,
            'name'           => $practice->name,
            'phone'          => $faker->phoneNumber,
            //                'fax',
            'address_line_1' => $faker->address,
            'address_line_2' => $faker->address,
            'city'           => $faker->city,
            //            'state' => $faker->state??,
            'timezone'       => $faker->timezone,
            'postal_code'    => $faker->postcode,
            //                'ehr_login',
            //                'ehr_password',
        ]);

        return $location;
    }

    /**
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createPractice()
    {

        $faker = Factory::create();
        $name  = $faker->company;

        $practice = Practice::create([
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
        ]);

        return $practice;
    }


    /**
     * @param Practice $practice
     *
     * @return \App\User
     */
    public function createPatient(Practice $practice, $providerId)
    {
        $roles = [
            Role::whereName('participant')->first()->id,
        ];

        $patient = $this->setupUser($practice->id, $roles);
        $date    = Carbon::now();

        //status
        $status = collect([Patient::ENROLLED, Patient::PAUSED, Patient::WITHDRAWN]);
        $patient->patientInfo->setCcmStatusAttribute($status->random());


        //attach problems, summaries, chargeable services
        $problemIds = CpmProblem::get()->pluck('id');
        $months     = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $patient->patientSummaries()->create([
            'month_year' => Carbon::now()->copy()->subMonth($months->random())->startOfMonth()->toDateString(),
            'ccm_time'   => 1400,
            'approved'   => 1,
            'actor_id'   => 1,
        ]);
        $patient->chargeableServices()->attach(1);
        $patient->cpmProblems()->attach($problemIds->random(5)->all());

        //careplan
        $patient->carePlan()->create([
            'mode'                  => CarePlan::WEB,
            'care_plan_template_id' => 1,
            'status'                => CarePlan::DRAFT,
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
                'type'          => $activityType->random(),
                'duration'      => $activityDuration->random(),
                'duration_unit' => 'seconds',
                'performed_at'  => $date->copy()->subDay(5)->toDateTimeString(),
            ],
            [
                'type'          => $activityType->random(),
                'duration'      => $activityDuration->random(),
                'duration_unit' => 'seconds',
                'performed_at'  => $date->copy()->subDay(7)->toDateTimeString(),
            ],
        ]);

        $patient->setBillingProviderIdAttribute($providerId);


        $patient->load(
            'patientInfo',
            'activities',
            'patientSummaries',
            'cpmProblems',
            'chargeableServices',
            'carePlan',
            'billingProvider');

        return $patient;

    }

    /**
     * @param Practice $practice
     *
     * @return \App\User
     */
    public function createProvider(Practice $practice)
    {
        $roles = [
            Role::whereName('provider')->first()->id,
        ];

        $provider = $this->setupUser($practice->id, $roles);

        return $provider;
    }

    /**
     * @param int $patientCount
     *
     * @return mixed
     */
    public function createTestCustomerData($patientCount = 50)
    {

        $practice = $this->createPractice();
        $location = $this->createLocation($practice);
        $provider = $this->createProvider($practice);
        $patients = [];


        for ($x = $patientCount; $x > 0; $x--) {
            $patients[] = $this->createPatient($practice, $provider->id);
        }

        $patients = collect($patients);

        $data['practice'] = $practice;
        $data['location'] = $location;
        $data['patients'] = $patients;
        $data['provider'] = $provider;

        return $data;

    }


}
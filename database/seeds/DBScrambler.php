<?php

use App\Appointment;
use App\Call;
use App\CareplanAssessment;
use App\Location;
use App\Models\Addendum;
use App\Models\CCD\CcdInsurancePolicy;
use App\Note;
use App\PhoneNumber;
use App\Practice;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DBScrambler extends Seeder
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $this->throwExceptionIfProduction();

        $limit = ini_get('memory_limit'); // retrieve the set limit
        ini_set('memory_limit', -1); // remove memory limit

        $this->scrambleDB();

        ini_set('memory_limit', $limit);
    }

    /**
     * @throws Exception
     */
    private function throwExceptionIfProduction()
    {
        if (in_array(app()->environment(), [
            'production',
            'worker',
        ])) {
            $env = app()->environment();

            throw new \Exception("Not a good idea to run this on environment $env");
        }

        if (in_array(env('DB_DATABASE'), [
            'cpm_production',
        ])) {
            $db = env('DB_DATABASE');

            throw new \Exception("Not a good idea to run this on DB $db");
        }
    }

    public function scrambleDB()
    {
        //scramble practices
        Practice::withTrashed()
                ->get()
                ->chunk(100, function ($practices) {
                    foreach ($practices as $practice) {
                        $fakePractice = \factory(Practice::class)->make();

                        $practice->name                     = $fakePractice->name;
                        $practice->display_name             = $fakePractice->display_name;
                        $practice->federal_tax_id           = $fakePractice->federal_tax_id;
                        $practice->sms_marketing_number     = $fakePractice->sms_marketing_number;
                        $practice->weekly_report_recipients = $fakePractice->weekly_report_recipients;
                        $practice->invoice_recipients       = $fakePractice->invoice_recipients;
                        $practice->bill_to_name             = $fakePractice->bill_to_name;
                        $practice->outgoing_phone_number    = $fakePractice->outgoing_phone_number;

                        $saved = $practice->save();
                    }
                });

        Location::withTrashed()
                ->get()
                ->chunk(100, function ($locations) {
                    foreach ($locations as $location) {
                        $location->name           = $this->faker->company;
                        $location->phone          = formatPhoneNumber($this->faker->phoneNumber);
                        $location->fax            = formatPhoneNumber($this->faker->phoneNumber);
                        $location->address_line_1 = $this->faker->address;
                        $location->address_line_2 = $this->faker->secondaryAddress;
                        $location->city           = $this->faker->city;
                        $location->state          = $this->faker->stateAbbr;
                        $location->postal_code    = $this->faker->postcode;
                        $location->ehr_login      = formatPhoneNumber($this->faker->phoneNumber);
                        $location->ehr_password   = formatPhoneNumber($this->faker->phoneNumber);

                        $saved = $location->save();
                    }
                });

        PhoneNumber::get()
                   ->chunk(100, function ($phones) {
                       foreach ($phones as $phone) {
                           $phone->number = formatPhoneNumber($this->faker->phoneNumber);

                           $saved = $phone->save();
                       }
                   });


        Call::where('id', '>', 0)
            ->update([
                'inbound_phone_number'  => formatPhoneNumber($this->faker->phoneNumber),
                'outbound_phone_number' => formatPhoneNumber($this->faker->phoneNumber),
            ]);


        CcdInsurancePolicy::withTrashed()
                          ->get()
                          ->chunk(1000, function ($policies) {
                              foreach ($policies as $policy) {
                                  $fakeInsurance = \factory(Practice::class)->make();

                                  $policy->name       = $fakeInsurance->name;
                                  $policy->type       = $fakeInsurance->type;
                                  $policy->policy_id  = $fakeInsurance->policy_id;
                                  $policy->relation   = $fakeInsurance->relation;
                                  $policy->subscriber = $fakeInsurance->subscriber;

                                  $saved = $policy->save();
                              }
                          });

        Addendum::where('id', '>', 0)
                ->update([
                    'body' => $this->faker->text(),
                ]);

        Appointment::where('id', '>', 0)
                   ->update([
                       'comment' => $this->faker->text(),
                   ]);

        CareplanAssessment::where('id', '>', 0)
                          ->update([
                              'alcohol_misuse_counseling'           => $this->faker->text(),
                              'diabetes_screening_interval'         => $this->faker->text(),
                              'diabetes_screening_risk'             => $this->faker->text(),
                              'key_treatment'                       => $this->faker->text(),
                              'patient_functional_assistance_areas' => $this->faker->text(),
                              'patient_psychosocial_areas_to_watch' => $this->faker->text(),
                              'risk'                                => $this->faker->text(),
                              'risk_factors'                        => $this->faker->text(),
                              'tobacco_misuse_counseling'           => $this->faker->text(),
                          ]);

        Note::where('id', '>', 0)
            ->update([
                'body' => $this->faker->text(),
            ]);

        $this->truncateTables();

        Artisan::call('db:seed', [
            '--class' => CreateTesterUsersSeeder::class,
        ]);
    }

    private function truncateTables()
    {
        $tables = [
            'ccdas',
            'ccd_allergy_logs',
            'ccd_demographics_logs',
            'ccd_document_logs',
            'ccd_medication_logs',
            'ccd_problem_logs',
            'ccd_provider_logs',
            'insurance_logs',
            'allergy_imports',
            'demographics_imports',
            'medication_imports',
            'problem_imports',
            'ccda_requests',
            'eligibility_batches',
            'eligibility_jobs',
            'emr_direct_addresses',
            'exceptions',
            'failed_jobs',
            'fax_logs',
            'foreign_ids',
            'imported_medical_records',
            'invites',
            'jobs',
            'lgh_insurance',
            'lgh_insurance_old',
            'lgh_providers',
            'lgh_valid_patients',
            'lv_password_resets',
            'patient_reports',
            'patient_signups',
            'pdfs',
            'phoenix_heart_allergies',
            'phoenix_heart_insurances',
            'phoenix_heart_medications',
            'phoenix_heart_names',
            'phoenix_heart_problems',
            'processed_files',
            'enrollees',
            'rappa_datas',
            'rappa_ins_allergies',
            'rappa_names',
            'revisions',
            'tabular_medical_records',
            'target_patients',
            'usermeta',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

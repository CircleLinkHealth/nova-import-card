<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Traits;

trait SeedEligibilityJobsForEnrollees
{
    /**
     * @param $enrollees
     */
    public function seedEligibilityJobs($enrollees)
    {
        foreach ($enrollees as $enrollee) {
            //create eligibility job
            $job       = factory(\CircleLinkHealth\Eligibility\Entities\EligibilityJob::class)->create();
            $job->hash = $enrollee->practice->name.$enrollee->first_name.$enrollee->last_name.$enrollee->mrn.$enrollee->city.$enrollee->state.$enrollee->zip;

            $job->data = [
                'patient_id'              => $enrollee->mrn,
                'last_name'               => $enrollee->last_name,
                'first_name'              => $enrollee->first_name,
                'dob'                     => $enrollee->dob->toDateString(), // its dob in importer v-3
                'gender'                  => collect(['M', 'F'])->random(),
                'lang'                    => $enrollee->lang,
                'preferred_provider'      => $enrollee->providerFullName,
                'cell_phone'              => $enrollee->cell_phone,
                'home_phone'              => $enrollee->home_phone,
                'other_phone'             => $enrollee->other_phone,
                'primary_phone'           => null,
                'email'                   => $enrollee->email,
                'street'                  => $enrollee->address,
                'address_line_1'          => $enrollee->address,
                'street2'                 => $enrollee->address_2,
                'address_line_2'          => $enrollee->address_2,
                'city'                    => $enrollee->city,
                'state'                   => $enrollee->state,
                'zip'                     => $enrollee->zip,
                'postal_code'             => $enrollee->zip,
                'primary_insurance'       => $enrollee->primary_insurance,
                'secondary_insurance'     => $enrollee->secondary_insturance,
                'referring_provider_name' => $enrollee->referring_provider_name,
                'mrn'                     => $enrollee->mrn,
                'problems_string'                => [
                    [
                        'name'       => 'Hypertension',
                        'start_date' => \Carbon\Carbon::now()->toDateString(),
                        'code'       => 'I10',
                        'code_type'  => 'ICD-10',
                    ],
                    [
                        'name'       => 'Asthma',
                        'start_date' => \Carbon\Carbon::now()->toDateString(),
                        'code'       => 'J45.901',
                        'code_type'  => 'ICD-10',
                    ],
                ],
                'allergies_string'   => [['name' => 'peanut']],
                'medications_string' => [[
                    'name' => 'Test Aspirin',
                ]],
                'is_demo'     => 'true',
            ];
            $job->save();

            $enrollee->eligibility_job_id = $job->id;
            $enrollee->save();
        }

        $this->command->info('Seeding possible family members');
        //take some enrollees to fake "suggested" family members for testing purposes. Randomly make their data look like the would be family members
        [$enrollees, $fakeSuggestedFamilyMembers] = $enrollees->partition(function ($e) use ($enrollees) {
            return $enrollees->search($e) < 3;
        });

        if ( ! empty($enrollees)) {
            $fakeSuggestedFamilyMembers->each(function ($e) use ($enrollees) {
                $family = $enrollees->random();

                $rand = rand(0, 10);
                switch ($rand) {
                    case $rand < 2:
                        $e->address = $family->address;
                        break;
                    case $rand >= 2 && $rand < 5:
                        $e->address_2 = $family->address;
                        break;
                    case $rand >= 5 && $rand < 7:
                        $e->home_phone = $family->cell_phone;
                        break;
                    case $rand >= 7 && $rand < 9:
                        $e->cell_phone = $family->home_phone;
                        break;
                    case 10 == $rand:
                        $e->other_phone = $family->cell_phone;
                        break;
                }
                $e->save();
            });
        }
        $this->command->info('Enrollee Family Members seeded.');
    }
}

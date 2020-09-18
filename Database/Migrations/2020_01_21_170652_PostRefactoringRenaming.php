<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class PostRefactoringRenaming extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (app()->environment('testing')) {
            return;
        }
        \DB::table('instructables')
            ->where('instructable_type', 'App\Models\CPM\CpmProblem')
            ->update(
                [
                    'instructable_type' => 'CircleLinkHealth\SharedModels\Entities\CpmProblem',
                ]
            );

        \DB::table('pdfs')
            ->where('pdfable_type', 'App\CarePlan')
            ->update(
                [
                    'pdfable_type' => 'CircleLinkHealth\SharedModels\Entities\CarePlan',
                ]
            );

        collect([
            [
                'old' => 'App\Models\CPM\CpmProblem',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmProblem',
            ],
            [
                'old' => 'App\AppConfig',
                'new' => 'CircleLinkHealth\Core\Entities\AppConfig',
            ],
            [
                'old' => 'CircleLinkHealth\SharedModels\Entities\CarePlan',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CarePlan',
            ],
            [
                'old' => 'App\Models\CCD\Allergy',
                'new' => 'CircleLinkHealth\SharedModels\Entities\Allergy',
            ],
            [
                'old' => 'App\Importer\Models\ItemLogs\AllergyLog',
                'new' => 'CircleLinkHealth\SharedModels\Entities\AllergyLog',
            ],
            [
                'old' => 'App\Models\MedicalRecords\Ccda',
                'new' => 'CircleLinkHealth\SharedModels\Entities\Ccda',
            ],
            [
                'old' => 'App\Models\CCD\CcdInsurancePolicy',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy',
            ],
            [
                'old' => 'App\Models\CPM\CpmBiometricUser',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmBiometricUser',
            ], [
                'old' => 'App\Models\CPM\Biometrics\CpmBloodPressure',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmBloodPressure',
            ],
            [
                'old' => 'App\Models\CPM\Biometrics\CpmBloodSugar',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmBloodSugar',
            ],
            [
                'old' => 'App\Models\CPM\CpmInstruction',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmInstruction',
            ],
            [
                'old' => 'App\Models\CPM\CpmLifestyleUser',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmLifestyleUser',
            ],
            [
                'old' => 'App\Models\CPM\CpmMiscUser',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmMiscUser',
            ], [
                'old' => 'App\Models\CPM\CpmSymptomUser',
                'new' => 'CircleLinkHealth\SharedModels\Entities\CpmSymptomUser',
            ],
            [
                'old' => 'App\Models\CCD\Medication',
                'new' => 'CircleLinkHealth\SharedModels\Entities\Medication',
            ],
            [
                'old' => 'App\Models\CCD\Problem',
                'new' => 'CircleLinkHealth\SharedModels\Entities\Problem',
            ],
            [
                'old' => 'App\Models\ProblemCode',
                'new' => 'CircleLinkHealth\SharedModels\Entities\ProblemCode',
            ],
            [
                'old' => 'App\Models\MedicalRecords\TabularMedicalRecord',
                'new' => 'CircleLinkHealth\SharedModels\Entities\TabularMedicalRecord',
            ],
            [
                'old' => 'App\EligibilityBatch',
                'new' => 'CircleLinkHealth\Eligibility\Entities\EligibilityBatch',
            ],
            [
                'old' => 'App\EligibilityJob',
                'new' => 'CircleLinkHealth\Eligibility\Entities\EligibilityJob',
            ],
            [
                'old' => 'App\Enrollee',
                'new' => 'CircleLinkHealth\SharedModels\Entities\Enrollee',
            ],
        ])->each(function ($change) {
            echo "\nChanging {$change['old']} to {$change['new']}.\n";

            \DB::table('revisions')
                ->where('revisionable_type', $change['old'])
                ->update(
                    [
                        'revisionable_type' => $change['new'],
                    ]
                );
        });
    }
}

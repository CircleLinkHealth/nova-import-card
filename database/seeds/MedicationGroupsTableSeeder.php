<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Models\CPM\CpmMedicationGroup;
use Illuminate\Database\Seeder;

class MedicationGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            'Blood Pressure Meds',
            'Blood Thinners (Plavix, Aspirin)',
            'Breathing Meds for Asthma/COPD',
            'Cholesterol Meds',
            'Dementia Meds',
            'Insulin or other Injectable',
            'Kidney Disease Meds',
            'Mood/Depression Med',
            'Oral Diabetes Meds',
            'Water Pills/Diuretics',
        ] as $name) {
            CpmMedicationGroup::updateOrCreate(
                [
                    'name' => $name,
                ]
            );
        }
    }
}

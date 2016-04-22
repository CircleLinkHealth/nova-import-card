<?php

class CpmMedicationsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $medications = array (
            0 => 
            array (
                'name' => 'Blood Pressure Meds',
            ),
            1 =>
            array (
                'name' => 'Cholesterol Meds',
            ),
            2 =>
            array (
                'name' => 'Blood Thinners (Plavix, Aspirin)',
            ),
            3 =>
            array (
                'name' => 'Water Pills/Diuretics',
            ),
            4 =>
            array (
                'name' => 'Oral Diabetes Meds',
            ),
            5 =>
            array (
                'name' => 'Insulin or other Injectable',
            ),
            6 =>
                array(
                    'name' => 'Medication List',
                ),
            7 =>
                array(
                    'name' => 'Breathing Meds for Asthma/COPD',
                ),
            8 =>
                array(
                    'name' => 'Dementia Meds',
                ),
            9 =>
                array(
                    'name' => 'Mood/Depression Meds',
                ),
        );

        foreach ($medications as $medication) {
            \App\Entities\CPM\CpmMedicationGroup::updateOrCreate($medication);
        }
    }
}

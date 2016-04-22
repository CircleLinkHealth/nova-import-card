<?php

class CpmProblemsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $problems = array (
            0 =>
            array (
                'name' => 'Hypertension',
            ),
            1 =>
            array (
                'name' => 'High Cholesterol',
            ),
            2 =>
            array (
                'name' => 'CAD',
            ),
            3 =>
            array (
                'name' => 'CHF',
            ),
            4 =>
            array (
                'name' => 'Kidney Disease',
            ),
            5 =>
            array (
                'name' => 'Afib',
            ),
            6 =>
                array(
                    'name' => 'Diabetes',
                ),
            7 =>
                array(
                    'name' => 'Asthma--COPD',
                ),
            8 =>
                array(
                    'name' => 'Depression',
                ),
            9 =>
                array(
                    'name' => 'Dementia',
                ),
            10 =>
                array(
                    'name' => 'Other Conditions',
                ),
            11 =>
                array(
                    'name' => 'Smoking',
                ),
        );

        foreach ($problems as $problem) {
            \App\Entities\CPM\CpmProblem::updateOrCreate($problem);
        }
    }
}

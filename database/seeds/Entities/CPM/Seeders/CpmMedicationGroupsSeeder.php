<?php

class CpmMedicationGroupsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $medications = [
            [
                'name' => 'Blood Pressure Meds',
            ],
            [
                'name' => 'Cholesterol Meds',
            ],
            [
                'name' => 'Blood Thinners (Plavix, Aspirin)',
            ],
            [
                'name' => 'Water Pills/Diuretics',
            ],
            [
                'name' => 'Kidney Disease Meds',
            ],
            [
                'name' => 'Oral Diabetes Meds',
            ],
            [
                'name' => 'Insulin or other Injectable',
            ],
            [
                'name' => 'Breathing Meds for Asthma/COPD',
            ],
            [
                'name' => 'Dementia Meds',
            ],
            [
                'name' => 'Mood/Depression Meds',
            ],
        ];

        foreach ($medications as $medication) {

            $careItem = \App\CareItem::whereDisplayName($medication['name'])->first();

            $medGroup = \App\Models\CPM\CpmMedicationGroup::updateOrCreate($medication, [
                'care_item_id' => $careItem->id,
            ]);

            $careItem->type = \App\Models\CPM\CpmMedicationGroup::class;
            $careItem->type_id = $medGroup->id;
            $careItem->save();

            $userValues = \App\CareItemUserValue::whereCareItemId($careItem->id)->get();

            foreach ($userValues as $v) {
                $v->type = \App\Models\CPM\CpmMedicationGroup::class;
                $v->type_id = $medGroup->id;
                $v->save();
            }
        }
    }
}

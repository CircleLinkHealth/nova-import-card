<?php

class CpmSymptomsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $symptoms = [
            [
                'name' => 'Shortness of breath',
            ],
            [
                'name' => 'Coughing/wheezing',
            ],
            [
                'name' => 'Chest pain/tightness',
            ],
            [
                'name' => 'Fatigue',
            ],
            [
                'name' => 'Weakness/dizziness',
            ],
            [
                'name' => 'Swelling in legs/feet',
            ],
            [
                'name' => 'Feeling down/sleep changes',
            ],
            [
                'name' => 'Sweating',
            ],
            [
                'name' => 'Palpitations',
            ],
            [
                'name' => 'Pain',
            ],
            [
                'name' => 'Anxiety',
            ],
        ];

        foreach ($symptoms as $symptom) {
            $careItem = \App\CareItem::whereDisplayName($symptom['name'])->first();

            $cpmSymptom = \App\Models\CPM\CpmSymptom::updateOrCreate($symptom, [
                'care_item_id' => $careItem->id,
            ]);

            $careItem->type = \App\Models\CPM\CpmSymptom::class;
            $careItem->type_id = $cpmSymptom->id;
            $careItem->save();

            $userValues = \App\CareItemUserValue::whereCareItemId($careItem->id)->get();

            foreach ($userValues as $v) {
                $v->type = \App\Models\CPM\CpmSymptom::class;
                $v->type_id = $cpmSymptom->id;
                $v->save();
            }

            $this->command->info("\tAdded " . $symptom['name']);
        }
    }
}

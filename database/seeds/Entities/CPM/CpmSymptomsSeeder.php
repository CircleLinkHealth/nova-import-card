<?php

class CpmSymptomsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $symptoms = array(
            0 =>
                array(
                    'name' => 'Shortness of breath',
                ),
            1 =>
                array(
                    'name' => 'Coughing/wheezing',
                ),
            2 =>
                array(
                    'name' => 'Chest pain/tightness',
                ),
            3 =>
                array(
                    'name' => 'Fatigue',
                ),
            4 =>
                array(
                    'name' => 'Weakness/dizziness',
                ),
            5 =>
                array(
                    'name' => 'Hyperglycemia(high blood sugar) thirsty, headaches, fatigue',
                ),
            6 =>
                array(
                    'name' => 'Swelling in legs/feet',
                ),
            7 =>
                array(
                    'name' => 'Feeling down/sleep changes',
                ),
            8 =>
                array(
                    'name' => 'Sweating',
                ),
            9 =>
                array(
                    'name' => 'Palpitations',
                ),
            10 =>
                array(
                    'name' => 'Pain',
                ),
            11 =>
                array(
                    'name' => 'Anxiety',
                ),
        );

        foreach ($symptoms as $symptom) {
            $careItem = \App\CareItem::whereDisplayName($symptom['name'])->first();

            $cpmSymptom = \App\Entities\CPM\CpmSymptom::updateOrCreate($symptom, [
                'care_item_id' => $careItem->id,
            ]);

            $careItem->type = \App\Entities\CPM\CpmSymptom::class;
            $careItem->type_id = $cpmSymptom->id;
            $careItem->save();
        }
    }
}

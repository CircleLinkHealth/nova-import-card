<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/27/16
 * Time: 5:16 PM
 */
class CpmBiometricsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $entities[] = [
            'name' => 'Weight',
            'type' => \App\Models\CPM\Biometrics\CpmWeight::class,
        ];

        $entities[] = [
            'name' => 'Blood Pressure',
            'type' => \App\Models\CPM\Biometrics\CpmBloodPressure::class,
        ];

        $entities[] = [
            'name' => 'Blood Sugar',
            'type' => \App\Models\CPM\Biometrics\CpmBloodSugar::class,
        ];

        $entities[] = [
            'name' => 'Smoking (# per day)',
            'type' => \App\Models\CPM\Biometrics\CpmSmoking::class,
        ];

        foreach ($entities as $entity) {

            $careItem = \App\CareItem::whereDisplayName($entity['name'])->first();

            //relate lifestyle to care item
            $biometric = \App\Models\CPM\CpmBiometric::updateOrCreate($entity, [
                'care_item_id' => $careItem->id,
            ]);

            //relate care item to lifestyle
            $careItem->type = \App\Models\CPM\CpmBiometric::class;
            $careItem->type_id = $biometric->id;
            $careItem->save();

            $careItemCarePlan = \App\CarePlanItem::whereItemId($careItem->id)->get();

            foreach ($careItemCarePlan as $val)
            {
                $val->type = \App\Models\CPM\CpmBiometric::class;
                $val->type_id = $biometric->id;
                $val->save();
            }
        }
    }
}
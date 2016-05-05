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
            'type' => 0,
        ];

        $entities[] = [
            'name' => 'Blood Pressure',
            'type' => 1,
        ];

        $entities[] = [
            'name' => 'Blood Sugar',
            'type' => 2,
        ];

        $entities[] = [
            'name' => 'Smoking (# per day)',
            'type' => 3,
        ];

        foreach ($entities as $entity) {

            $careItem = \App\CareItem::whereDisplayName($entity['name'])->first();

            //relate biometric to care item
            $biometric = \App\Models\CPM\CpmBiometric::updateOrCreate([
                'name' => $entity['name']
            ], [
                'care_item_id' => $careItem->id,
                'type' => $entity['type']
            ]);

            //relate care item to biometric
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

            $userValues = \App\CareItemUserValue::whereCareItemId($careItem->id)->get();

            foreach ($userValues as $v) {
                $v->type = \App\Models\CPM\CpmBiometric::class;
                $v->type_id = $biometric->id;
                $v->save();
            }
        }
    }
}
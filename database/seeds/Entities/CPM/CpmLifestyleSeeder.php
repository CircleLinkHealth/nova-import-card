<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/21/16
 * Time: 11:44 AM
 */
class CpmLifestyleSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $entities[] = [
            'name' => 'Healthy Diet',
        ];

        $entities[] = [
            'name' => 'Low Salt Diet',
        ];

        $entities[] = [
            'name' => 'Diabetic Diet',
        ];

        $entities[] = [
            'name' => 'Exercise',
        ];

        foreach ($entities as $entity) {

            $careItem = \App\CareItem::whereDisplayName($entity['name'])->first();

            //relate lifestyle to care item
            $lifestyle = \App\Entities\CPM\CpmLifestyle::updateOrCreate($entity, [
                'care_item_id' => $careItem->id,
            ]);

            //relate care item to lifestyle
            $careItem->type = \App\Entities\CPM\CpmLifestyle::class;
            $careItem->type_id = $lifestyle->id;
            $careItem->save();

            $careItemCarePlan = \App\CarePlanItem::whereItemId($careItem->id)->get();

            foreach ($careItemCarePlan as $val)
            {
                $val->type = \App\Entities\CPM\CpmLifestyle::class;
                $val->type_id = $lifestyle->id;
                $val->save();
            }


        }
    }

}
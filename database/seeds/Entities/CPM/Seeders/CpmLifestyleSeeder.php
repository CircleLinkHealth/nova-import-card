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
            $lifestyle = \App\Models\CPM\CpmLifestyle::updateOrCreate($entity, [
                'care_item_id' => $careItem->id,
            ]);

            //relate care item to lifestyle
            $careItem->type = \App\Models\CPM\CpmLifestyle::class;
            $careItem->type_id = $lifestyle->id;
            $careItem->save();

            $this->command->info("\tAdded " . $entity['name']);

        }
    }

}
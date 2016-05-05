<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 10:17 AM
 */
class CpmMiscSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $entities[] = [
            'name' => 'Other Conditions',
        ];

        $entities[] = [
            'name' => 'Medication List',
        ];

        $entities[] = [
            'name' => 'Track Care Transitions',
        ];

        $entities[] = [
            'name' => 'Old Meds List',
        ];

        $entities[] = [
            'name' => 'Allergies',
        ];

        $entities[] = [
            'name' => 'Social Services',
        ];

        $entities[] = [
            'name' => 'Appointments',
        ];

        $entities[] = [
            'name' => 'Other',
        ];

        foreach ($entities as $entity) {

            $careItem = \App\CareItem::whereDisplayName($entity['name'])->first();

            //relate lifestyle to care item
            $misc = \App\Models\CPM\CpmMisc::updateOrCreate($entity, [
                'care_item_id' => $careItem->id,
            ]);

            //relate care item to lifestyle
            $careItem->type = \App\Models\CPM\CpmMisc::class;
            $careItem->type_id = $misc->id;
            $careItem->save();

            $this->command->info("\tAdded " . $entity['name']);


        }
    }

}
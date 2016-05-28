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
            'detailsCareItemName' => 'other-conditions-details',
        ];

        $entities[] = [
            'name' => 'Medication List',
            'detailsCareItemName' => 'medication-list-details',
        ];

        $entities[] = [
            'name' => 'Track Care Transitions',
            'detailsCareItemName' => '',
        ];

        $entities[] = [
            'name' => 'Allergies',
            'detailsCareItemName' => 'allergies-details',
        ];

        $entities[] = [
            'name' => 'Social Services',
            'detailsCareItemName' => 'social-services-details',
        ];

        $entities[] = [
            'name' => 'Appointments',
            'detailsCareItemName' => 'appointments-details',
        ];

        $entities[] = [
            'name' => 'Other',
            'detailsCareItemName' => 'other-details',
        ];

        foreach ($entities as $entity) {

            $careItem = (new \App\CareItem)->whereDisplayName($entity['name'])->first();
            
            $details = (new \App\CareItem)->whereName($entity['detailsCareItemName'])->first();

            //relate lifestyle to care item
            $misc = \App\Models\CPM\CpmMisc::updateOrCreate([ 'name' => $entity['name'] ], [
                'care_item_id' => $careItem->id,
                'details_care_item_id' => $details ? $details->id : null,
            ]);

            //relate care item to lifestyle
            $careItem->type = \App\Models\CPM\CpmMisc::class;
            $careItem->type_id = $misc->id;
            $careItem->save();

            $this->command->info("\tAdded " . $entity['name']);


        }
    }

}
<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/6/16
 * Time: 2:41 PM
 */
class UserBiometricsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        //CpmWeight
        $entities[] = [
            'type' => \App\Models\CPM\Biometrics\CpmWeight::class,
            'care_item_names' => [
                'starting' => 'weight-starting-weight',
                'target' => 'weight-target-weight',
                'monitor_changes_for_chf' => 'weight-monitor-weight-changes-for-chf',
            ],
            'relationship_fn_name' => 'cpmWeight',
        ];

        //CpmBloodSugar
        $entities[] = [
            'type' => \App\Models\CPM\Biometrics\CpmBloodSugar::class,
            'care_item_names' => [
                'starting' => 'blood-sugar-starting-bs',
                'target' => 'blood-sugar-target-bs',
                'starting_a1c' => 'blood-sugar-starting-a1c',
                'high_alert' => 'blood-sugar-bs-high-alert',
                'low_alert' => 'blood-sugar-bs-low-alert',
            ],
            'relationship_fn_name' => 'cpmBloodSugar',
        ];

        //CpmBloodPressure
        $entities[] = [
            'type' => \App\Models\CPM\Biometrics\CpmBloodPressure::class,
            'care_item_names' => [
                'starting' => 'blood-pressure-starting-bp',
                'target' => 'blood-pressure-target-bp',
                'systolic_high_alert' => 'blood-pressure-systolic-high-alert',
                'systolic_low_alert' => 'blood-pressure-systolic-low-alert',
                'diastolic_high_alert' => 'blood-pressure-diastolic-high-alert',
                'diastolic_low_alert' => 'blood-pressure-diastolic-low-alert',
            ],
            'relationship_fn_name' => 'cpmBloodPressure',
        ];

        //CpmSmoking
        $entities[] = [
            'type' => \App\Models\CPM\Biometrics\CpmSmoking::class,
            'care_item_names' => [
                'starting' => 'smoking-per-day-starting-count',
                'target' => 'smoking-per-day-target-count',

            ],
            'relationship_fn_name' => 'cpmSmoking',
        ];

        foreach ($entities as $entity) {
            foreach ($entity['care_item_names'] as $modelFieldName => $careItemName) {
                $careItem = \App\CareItem::whereName($careItemName)->first();

                //relate care item to lifestyle
                $careItem->type = $entity['type'];
                $careItem->model_field_name = $modelFieldName;
                $careItem->relationship_fn_name = $entity['relationship_fn_name'];
                $careItem->save();


                $userVal = \App\CareItemUserValue::whereCareItemId($careItem->id)
                    ->update([
                        'type' => $entity['type'],
                        'model_field_name' => $modelFieldName,
                        'relationship_fn_name' => $entity['relationship_fn_name'],
                    ]);


                $cpi = \App\CarePlanItem::whereItemId($careItem->id)->update([
                    'type' => $entity['type'],
                    'model_field_name' => $modelFieldName,
                    'relationship_fn_name' => $entity['relationship_fn_name'],
                ]);
            }
        }
    }
}
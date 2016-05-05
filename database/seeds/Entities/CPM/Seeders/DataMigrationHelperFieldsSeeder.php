<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/5/16
 * Time: 4:52 PM
 */
class DataMigrationHelperFieldsSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $models[] = \App\Models\CPM\CpmBiometric::class;
        $models[] = \App\Models\CPM\CpmLifestyle::class;
        $models[] = \App\Models\CPM\CpmMedicationGroup::class;
        $models[] = \App\Models\CPM\CpmMisc::class;
        $models[] = \App\Models\CPM\CpmSymptom::class;

        foreach ($models as $model) {
            $instance = app($model);

            $all = $instance->all();

            foreach ($all as $row) {
                //care item user value

                $userValues = \App\CareItemUserValue::whereCareItemId($row->care_item_id)->get();

                DB::transaction(function () use ($userValues, $model, $row) {
                    foreach ($userValues as $v) {
                        $v->type = $model;
                        $v->type_id = $row->id;
                        $v->save();
                    }
                });

                //care plan values

                $careItemCarePlan = \App\CarePlanItem::whereItemId($row->care_item_id)->get();

                DB::transaction(function () use ($careItemCarePlan, $model, $row) {
                    foreach ($careItemCarePlan as $val) {
                        $val->type = $model;
                        $val->type_id = $row->id;
                        $val->save();
                    }
                });

                $this->command->info("\tAdded " . $row->name);
            }


        }
    }
}
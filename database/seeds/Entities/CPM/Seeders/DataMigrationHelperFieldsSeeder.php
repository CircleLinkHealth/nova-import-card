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
        $models[] = ['name' => \App\Models\CPM\CpmBiometric::class, 'relationship_fn_name' => 'cpmBiometrics'];
        $models[] = ['name' => \App\Models\CPM\CpmLifestyle::class, 'relationship_fn_name' => 'cpmLifestyles'];
        $models[] = ['name' => \App\Models\CPM\CpmMedicationGroup::class, 'relationship_fn_name' => 'cpmMedicationGroups'];
        $models[] = ['name' => \App\Models\CPM\CpmMisc::class, 'relationship_fn_name' => 'cpmMiscs'];
        $models[] = ['name' => \App\Models\CPM\CpmSymptom::class, 'relationship_fn_name' => 'cpmSymptoms'];
        $models[] = ['name' => \App\Models\CPM\CpmProblem::class, 'relationship_fn_name' => 'cpmProblems'];

        foreach ($models as $model) {
            $instance = app($model['name']);

            $all = $instance->all();

            foreach ($all as $row) {
                //care item user value

                $userValues = \App\CareItemUserValue::whereCareItemId($row->care_item_id)->get();

                DB::transaction(function () use ($userValues, $model, $row) {
                    foreach ($userValues as $v) {
                        $v->type = $model['name'];
                        $v->type_id = $row->id;
                        $v->relationship_fn_name = $model['relationship_fn_name'];
                        $v->save();
                    }
                });

                //care plan values

                $careItemCarePlan = \App\CarePlanItem::whereItemId($row->care_item_id)->get();

                DB::transaction(function () use ($careItemCarePlan, $model, $row) {
                    foreach ($careItemCarePlan as $val) {
                        $val->type = $model['name'];
                        $val->type_id = $row->id;
                        $val->relationship_fn_name = $model['relationship_fn_name'];
                        $val->save();
                    }
                });

                $this->command->info("\tAdded " . $row->name);
            }


        }
    }
}
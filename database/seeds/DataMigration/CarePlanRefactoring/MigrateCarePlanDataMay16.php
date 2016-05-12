<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/4/16
 * Time: 11:06 AM
 */
class MigrateCarePlanDataMay16 extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $users = \App\CareItemUserValue::distinct()->lists('user_id');

        DB::transaction(function () use ($users) {
            foreach ($users as $userId) {

                $user = \App\User::find($userId);

                if (empty($user)) {
                    $this->command->error("\tUser with id $userId does not exist");
                    continue;
                }

                try {
                    $this->migrateCpmEntities($user, $userId);

                    $this->migrateUserBiometrics($user, $userId);

                    $this->migrateCpmMiscsDetails($user, $userId);
                } catch (Illuminate\Database\QueryException $e) {
                    Log::alert($e);
                }

            }
        });
    }

    public function migrateCpmEntities(\App\User $user, $userId)
    {
        $userValues = \App\CareItemUserValue::whereNotNull('type')
            ->whereNotNull('type_id')
            ->whereNotNull('relationship_fn_name')
            ->whereValue('Active')
            ->whereUserId($userId)
            ->get()
            ->groupBy('relationship_fn_name')
            ->toArray();

        foreach ($userValues as $relationship => $v) {
            if (!method_exists($user, $relationship)) {
                $this->command->error("\tRelationship $relationship does not exist on User");
                continue;
            }

            $ids = array_column($userValues[$relationship], 'type_id');

            try {
                $user->{$relationship}()->sync($ids);
                $this->command->info("\tMigrated type $relationship for user with id $userId");
            } catch (Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1062) {
                    $this->command->error("Duplicate entry. Can't do that brah...");
                }
            }

            $cpt = $user->service()->firstOrDefaultCarePlan($user)->carePlanTemplate()->first();

            $cptRelated = $cpt->{$relationship}()->get()->keyBy('id');
            $userRelated = $user->{$relationship}()->get()->keyBy('id');

            if (!empty($userRelatedIds)) {
                foreach ($cptRelated as $templateRel) {
                    if (!isset($userRelatedIds[$templateRel->id])) continue;
                    if (empty($templateRel->pivot->cpm_instruction_id)) continue;

                    try {
                        \App\User::find($userRelated[$templateRel->id]->pivot->patient_id)->{$relationship}()->updateExistingPivot($templateRel->id, [
                            'cpm_instruction_id' => $templateRel->pivot->cpm_instruction_id,
                        ]);
                    } catch (\Exception $e) {
                        dd($e);
                    }
                }
            }

        }
    }


    public function migrateUserBiometrics(\App\User $user, $userId)
    {
        $userValues = \App\CareItemUserValue::whereNotNull('type')
            ->whereNotNull('relationship_fn_name')
            ->whereNotNull('model_field_name')
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->whereUserId($userId)
            ->get()
            ->groupBy('relationship_fn_name')
            ->toArray();

        foreach ($userValues as $relationship => $v) {
            if (!method_exists($user, $relationship)) {
                $this->command->error("\tRelationship $relationship does not exist on User");
                continue;
            }

            $args = [];

            foreach ($v as $field) {
                $args[$field['model_field_name']] = $field['value'];
            }

            $user->{$relationship}()->create($args);
            $this->command->info("\tMigrated type $relationship for user with id $userId");

        }
    }

    public function migrateCpmMiscsDetails($user, $userId)
    {
        $cpmMiscs = \App\Models\CPM\CpmMisc::where('details_care_item_id', '!=', 0)->get();

        foreach ($cpmMiscs as $misc) {
            $details = \App\CareItemUserValue::whereCareItemId($misc->details_care_item_id)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->whereUserId($userId)
                ->first();

            if (empty($details)) continue;

            $instruction = \App\Models\CPM\CpmInstruction::where('name', 'like', "%{$details->value}%")->first();
            
            if (empty($instruction)) {
                $instruction = \App\Models\CPM\CpmInstruction::create([
                    'name' => $details->value
                ]);
            }

            $user->cpmMiscs()->updateExistingPivot($misc->id, [
                'cpm_instruction_id' => $instruction->id,
            ]);

            $this->command->info("\tMigrated misc $misc->name for user with id $userId");
        }
    }

}
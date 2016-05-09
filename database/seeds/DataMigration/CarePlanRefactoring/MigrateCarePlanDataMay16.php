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

}
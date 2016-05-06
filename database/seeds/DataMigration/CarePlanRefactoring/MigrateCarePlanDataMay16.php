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

                $userValues = \App\CareItemUserValue::whereNotNull('type')
                    ->whereNotNull('type_id')
                    ->whereNotNull('relationship_fn_name')
                    ->whereValue('Active')
                    ->whereUserId($userId)
                    ->get()
                    ->groupBy('relationship_fn_name')
                    ->toArray();

                foreach ($userValues as $relationship => $v)
                {
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
        });
    }

}
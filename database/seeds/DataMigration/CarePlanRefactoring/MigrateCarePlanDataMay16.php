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
        $userValues = \App\CareItemUserValue::whereNotNull('type')
            ->whereNotNull('type_id')
            ->whereNotNull('relationship_fn_name')
            ->whereNotNull('user_id')
            ->get();

        DB::transaction(function () use ($userValues) {
            foreach ($userValues as $v) {
                $user = \App\User::find($v->user_id);

                if (empty($user)) {
                    $this->command->error("\tUser with id $v->user_id does not exist");
                    continue;
                }

                if (!method_exists($user, $v->relationship_fn_name)) {
                    $this->command->error("\tRelationship $v->relationship_fn_name does not exist on User");
                    continue;
                }

                if ($v->value == 'Active')
                {
                    $user->{$v->relationship_fn_name}()->attach($v->type_id);
                    $this->command->info("\tMigrated $v->type with id $v->type_id");
                }
            }
        });
    }

}
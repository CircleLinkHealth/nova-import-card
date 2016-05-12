<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/4/16
 * Time: 11:06 AM
 */
class MigrateUserCpmProblemsInstructions extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $users = \App\User::with('cpmProblems')->get();

        DB::transaction(function () use ($users) {
            foreach ($users as $user) {
                $userId = $user->ID;

                try {
                    $this->migrateUserCpmProblems($user, $userId);

                } catch (Illuminate\Database\QueryException $e) {
                    Log::alert($e);
                }

            }
        });
    }

    public function migrateUserCpmProblems(\App\User $user, $userId)
    {
        $userValues = $user->cpmProblems;

        foreach ($userValues as $v) {

            $instruction = \App\Models\CPM\CpmInstruction::with([
                'cpmProblems' => function ($q) use ($v) {
                    $q->where('instructable_id', '=', $v->id);
                }
            ])->first();

            if (empty($instruction)) continue;

                try {
                    $user->cpmProblems()->updateExistingPivot($v->id, [
                        'cpm_instruction_id' => $instruction->id,
                    ]);
                    $this->command->info("\tMigrated instruction with id $v->id for user with id $userId");

                } catch (\Exception $e) {
                    dd($e);
                }

        }


    }
}
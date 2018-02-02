<?php

use App\Practice;
use App\User;
use Illuminate\Database\Migrations\Migration;

class TransformAutoAttachPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $updated = User::ofType('administrator')
                       ->update([
                           'auto_attach_programs' => true,
                       ]);

        $allPractices = Practice::active()
                                ->get();

        $adminRoleId = 1;


        $users = User::withTrashed()
            ->ofType('administrator')
            ->with(['roles', 'practices'])
            ->get()
            ->map(function ($user) use ($adminRoleId, $allPractices) {
                $deleted = DB::table('practice_role_user')
                  ->where('role_id', '=', $adminRoleId)
                  ->where('user_id', '=', $user->id)
                  ->delete();

                foreach ($allPractices as $practice) {
                    $inserted = DB::table('practice_role_user')
                      ->insert([
                          'role_id'    => $adminRoleId,
                          'user_id'    => $user->id,
                          'program_id' => $practice->id,
                      ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use App\User;
use Illuminate\Database\Migrations\Migration;

class MigrateRolesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        App\User::withTrashed()->with(['roles', 'practices'])->chunk(5000, function($users){
            foreach ($users as $user) {
                foreach ($user->practices as $practice) {
                    foreach ($user->roles as $role) {
                        $result = $user->practices()->updateExistingPivot($practice->id, [
                            'role_id'    => $role->id,
                        ]);
                    }
                }
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

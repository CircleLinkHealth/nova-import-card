<?php

use App\NurseInfo;
use App\User;
use Illuminate\Database\Migrations\Migration;

class AddNuresinfoToCarecenterUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add nurseinfo to carecenter users
        $nurses = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->get();

        foreach($nurses as $nurse) {
            if ($nurse->hasRole('care-center') && !$nurse->nurseInfo) {
                $nurseInfo = new NurseInfo;
                $nurseInfo->user_id = $nurse->id;
                $nurseInfo->save();
                $nurse->load('nurseInfo');
                echo $nurse->id . ' - add nurseinfo relation' . PHP_EOL;
            }
        }
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

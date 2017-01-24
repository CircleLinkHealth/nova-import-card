<?php

use App\PatientCareTeamMember;
use Illuminate\Database\Migrations\Migration;

class FillAlertField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (PatientCareTeamMember::where('type', '=', PatientCareTeamMember::SEND_ALERT_TO)->get() as $row) {
            PatientCareTeamMember::where('user_id', '=', $row->user_id)
                ->where('member_user_id', '=', $row->member_user_id)
                ->update(['alert' => true]);

            $row->delete();
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

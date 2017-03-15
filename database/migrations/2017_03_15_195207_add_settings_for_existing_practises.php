<?php

use App\Practice;
use Illuminate\Database\Migrations\Migration;

class AddSettingsForExistingPractises extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Practice::all() as $p) {
            $args['auto_approve_careplans'] = $p->auto_approve_careplans;
            $args['email_careplan_approval_reminders'] = $p->send_alerts;

            $p->notificationSettings()->create($args);
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

<?php

use App\Practice;
use App\Settings;
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
            $settings = new Settings([
                'auto_approve_careplans'            => $p->auto_approve_careplans,
                'email_careplan_approval_reminders' => $p->send_alerts,
                'dm_pdf_careplan'                   => true,
                'dm_pdf_notes'                      => true,
                'efax_pdf_careplan'                 => true,
                'efax_pdf_notes'                    => true,
                'email_note_was_forwarded'          => true,
            ]);

            $p->syncSettings($settings);
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

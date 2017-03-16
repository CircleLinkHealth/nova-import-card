<?php

use App\Practice;
use App\Settings;
use Illuminate\Database\Seeder;

class MigratePracticeSettings extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
}

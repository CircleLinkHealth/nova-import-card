<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/03/2017
 * Time: 1:24 AM
 */

namespace App\Traits;


use App\Settings;

trait HasSettings
{
    /**
     * Get the settings.
     */
    public function settings()
    {
        return $this->morphMany(Settings::class, 'settingsable', 'settingsable_type', 'settingsable_id');
    }

    /**
     * Updates or Creates settings.
     *
     * @param Settings $settings
     */
    public function syncSettings(Settings $settings)
    {
        Settings::updateOrCreate([
            'settingsable_type' => self::class,
            'settingsable_id'   => $this->id,
        ], [
            'auto_approve_careplans'            => $settings->auto_approve_careplans,
            'dm_pdf_careplan'                   => $settings->dm_pdf_careplan,
            'dm_pdf_notes'                      => $settings->dm_pdf_notes,
            'efax_pdf_careplan'                 => $settings->efax_pdf_careplan,
            'efax_pdf_notes'                    => $settings->efax_pdf_notes,
            'email_careplan_approval_reminders' => $settings->email_careplan_approval_reminders,
            'email_note_was_forwarded'          => $settings->email_note_was_forwarded,
        ]);
    }
}
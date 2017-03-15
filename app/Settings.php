<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        //Email Notifications
        'email_careplan_approval_reminders',
        'email_note_was_forwarded',

        //Efax Notifications
        'efax_pdf_careplan',
        'efax_pdf_notes',

        //Direct Mail Notifications
        'dm_pdf_careplan',
        'dm_pdf_notes',
    ];

    /**
     * Get all of the owning settingsable models.
     */
    public function settingsable()
    {
        return $this->morphTo('settingsable', 'settingsable_type', 'settingsable_id');
    }
}

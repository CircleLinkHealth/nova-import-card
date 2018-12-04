<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\Settings.
 *
 * @property int                                           $id
 * @property int                                           $settingsable_id
 * @property string                                        $settingsable_type
 * @property string                                        $careplan_mode
 * @property int                                           $auto_approve_careplans
 * @property int                                           $dm_pdf_careplan
 * @property int                                           $dm_pdf_notes
 * @property int                                           $dm_audit_reports
 * @property int                                           $email_careplan_approval_reminders
 * @property int                                           $email_note_was_forwarded
 * @property int                                           $email_weekly_report
 * @property int                                           $efax_pdf_careplan
 * @property int                                           $efax_pdf_notes
 * @property int                                           $efax_audit_reports
 * @property string                                        $default_target_bp
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $settingsable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereAutoApproveCareplans($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereCareplanMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDefaultTargetBp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDmAuditReports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDmPdfCareplan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDmPdfNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEfaxAuditReports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEfaxPdfCareplan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEfaxPdfNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEmailCareplanApprovalReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEmailNoteWasForwarded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEmailWeeklyReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereRnCanApproveCareplans($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereSettingsableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereSettingsableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Settings extends \App\BaseModel
{
    protected $fillable = [
        'careplan_mode',
        'auto_approve_careplans',

        //Direct Mail Notifications
        'dm_pdf_careplan',
        'dm_pdf_notes',
        'dm_audit_reports',

        //Efax Notifications
        'efax_pdf_careplan',
        'efax_pdf_notes',
        'efax_audit_reports',
        'note_font_size',

        //Email Notifications
        'email_careplan_approval_reminders',
        'email_note_was_forwarded',
        'email_weekly_report',

        'bill_to',
        'api_auto_pull',

        'twilio_enabled',
    ];
    protected $table = 'cpm_settings';

    public function notesChannels()
    {
        $channels = [];

        if ($this->email_note_was_forwarded) {
            $channels[] = 'Email';
        }

        if ($this->dm_pdf_notes) {
            $channels[] = 'DM';
        }

        if ($this->efax_pdf_notes) {
            $channels[] = 'eFax';
        }

        return $channels;
    }

    /**
     * Get all of the owning settingsable models.
     */
    public function settingsable()
    {
        return $this->morphTo('settingsable', 'settingsable_type', 'settingsable_id');
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Contracts\AttachableToNotification;
use App\Contracts\PdfReport;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\NoteForwarded;
use App\Traits\Addendumable;
use App\Traits\NotificationAttachable;
use Carbon\Carbon;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PdfReportTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Note.
 *
 * @property int                                                             $id
 * @property int                                                             $patient_id
 * @property int                                                             $author_id
 * @property string                                                          $summary
 * @property string                                                          $body
 * @property int                                                             $isTCM
 * @property int                                                             $did_medication_recon
 * @property \Carbon\Carbon                                                  $created_at
 * @property \Carbon\Carbon                                                  $updated_at
 * @property string                                                          $type
 * @property \Carbon\Carbon                                                  $performed_at
 * @property int|null                                                        $logger_id
 * @property \App\Models\Addendum[]|\Illuminate\Database\Eloquent\Collection $addendums
 * @property \CircleLinkHealth\Customer\Entities\User                        $author
 * @property \App\Call                                                       $call
 * @property \CircleLinkHealth\Customer\Entities\User                        $patient
 * @property \CircleLinkHealth\Customer\Entities\User                        $program
 * @property string                                                          $status
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereAuthorId($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereBody($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedAt($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereDidMedicationRecon($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereId($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereIsTCM($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereLoggerId($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note wherePatientId($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note wherePerformedAt($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereType($value)
 * @method   static                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Customer\Entities\User|null                                                                   $logger
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                     $revisionHistory
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note emergency($yes = true)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note filter(\App\Filters\QueryFilters $filters)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note forwarded(\Carbon\Carbon $from = null, \Carbon\Carbon $to = null, $excludePatientSupport = true)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note forwardedTo($notifiableType, $notifiableId, \Carbon\Carbon $from = null, \Carbon\Carbon $to = null)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note newModelQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note newQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note patientPractice($practiceId)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note query()
 * @property string|null                                                                                                     $summary_type
 * @property string|null                                                                                                     $deleted_at
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @method   static                                                                                                          bool|null forceDelete()
 * @method   static                                                                                                          \Illuminate\Database\Query\Builder|\App\Note onlyTrashed()
 * @method   static                                                                                                          bool|null restore()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereDeletedAt($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereStatus($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereSummary($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereSummaryType($value)
 * @method   static                                                                                                          \Illuminate\Database\Query\Builder|\App\Note withTrashed()
 * @method   static                                                                                                          \Illuminate\Database\Query\Builder|\App\Note withoutTrashed()
 * @property int|null                                                                                                        $addendums_count
 * @property int|null                                                                                                        $notifications_count
 * @property int|null                                                                                                        $revision_history_count
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Note whereSuccessStory($value)
 * @property int                                                                                                             $success_story
 */
class Note extends \CircleLinkHealth\Core\Entities\BaseModel implements PdfReport, AttachableToNotification
{
    use Addendumable;
    use Filterable;
    use NotificationAttachable;
    use PdfReportTrait;
    use SoftDeletes;
    const STATUS_COMPLETE = 'complete';
    const STATUS_DRAFT    = 'draft';
    // Note Summary types
    const SUMMARY_FYI  = 'FYI';
    const SUMMARY_TODO = 'To-do';

    protected $dates = [
        'performed_at',
    ];

    protected $fillable = [
        'patient_id',
        'author_id',
        'logger_id',
        'summary',
        'summary_type',
        'body',
        'isTCM',
        'type',
        'did_medication_recon',
        'performed_at',
        'status',
        'success_story',
    ];

    protected $table = 'notes';

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    public function call()
    {
        return $this->hasOne(Call::class);
    }

    public function editLink()
    {
        if (self::STATUS_DRAFT !== $this->status) {
            return null;
        }

        return route('patient.note.edit', [
            'patientId' => $this->patient_id,
            'noteId'    => $this->id,
        ]);
    }

    /**
     * Forwards note to CareTeam and/or Support.
     *
     * @param bool      $notifyCareteam
     * @param bool      $notifySupport
     * @param bool|null $force
     */
    public function forward(bool $notifyCareteam = null, bool $notifySupport = null, bool $force = false)
    {
        $this->load([
            'patient.primaryPractice.settings',
            'patient.patientInfo.location',
        ]);

        $recipients = collect();

        $cpmSettings = $this->patient->primaryPractice->cpmSettings();

        $patientBillingProviderUser = $this->patient->billingProviderUser();

        if ($notifyCareteam) {
            $recipients = $this->patient->getCareTeamReceivesAlerts();

            if ($force && $patientBillingProviderUser) {
                $recipients->push($patientBillingProviderUser);
            }
        }

        if ($notifySupport) {
            $recipients->push(User::find(PatientSupportUser::id()));
        }

        $channelsForLocation = [];

        if ($cpmSettings->efax_pdf_notes) {
            $channelsForLocation[] = FaxChannel::class;
        }

        if ($cpmSettings->dm_pdf_notes) {
            $channelsForLocation[] = DirectMailChannel::class;
        }

        $channelsForUsers = $channelsForLocation;

        if ($cpmSettings->email_note_was_forwarded) {
            $channelsForUsers[] = 'mail';
        }

        if ($force && empty($channelsForUsers)) {
            $channelsForUsers = [
                'mail',
                DirectMailChannel::class,
                FaxChannel::class,
            ];
        }

        // Notify Users
        $recipients->unique()
            ->values()
            ->map(function ($carePersonUser) use ($channelsForUsers) {
                optional($carePersonUser)->notify(new NoteForwarded($this, $channelsForUsers));
            });

        if ($force && empty($channelsForLocation)) {
            $channelsForLocation = [
                DirectMailChannel::class,
                FaxChannel::class,
            ];
        }

        if ( ! $notifyCareteam || empty($channelsForLocation)) {
            return;
        }

        // Notify location
        optional($this->patient->patientInfo->location)->notify(new NoteForwarded($this, $channelsForLocation));
    }

    public function link()
    {
        return route('patient.note.view', [
            'patientId' => $this->patient_id,
            'noteId'    => $this->id,
        ]);
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logger_id')->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function program()
    {
        return $this->belongsTo('CircleLinkHealth\Customer\Entities\User', 'author_id', 'id');
    }

    /**
     * Scope for notes that were emergencies (or not if you pass $yes = false).
     *
     * @param $builder
     */
    public function scopeEmergency($builder, bool $yes = true)
    {
        $builder->where('isTCM', '=', $yes);
    }

    /**
     * Scope for notes that were forwarded.
     *
     * @param $builder
     */
    public function scopeForwarded($builder, Carbon $from = null, Carbon $to = null, bool $excludePatientSupport = true)
    {
        $args = [];

        if ($from) {
            $args[] = ['created_at', '>=', $from->toDateTimeString()];
        }

        if ($to) {
            $args[] = ['created_at', '<=', $to->toDateTimeString()];
        }

        $builder->whereHas('notifications', function ($q) use ($args, $excludePatientSupport) {
            $q->where($args);

            if ($excludePatientSupport) {
                $q->where([
                    ['notifiable_id', '!=', PatientSupportUser::id()], //exclude patient support
                ]);
            }
        });
    }

    /**
     * Scope for notes that were forwarded to a specific notifiable.
     *
     * @param $builder
     * @param $notifiableType
     * @param $notifiableId
     */
    public function scopeForwardedTo($builder, $notifiableType, $notifiableId, Carbon $from = null, Carbon $to = null)
    {
        $args = [
            ['notifiable_type', '=', $notifiableType],
            ['notifiable_id', '=', $notifiableId],
        ];

        if ($from) {
            $args[] = ['created_at', '>=', $from->toDateTimeString()];
        }

        if ($to) {
            $args[] = ['created_at', '<=', $to->toDateTimeString()];
        }

        $builder->whereHas('notifications', function ($q) use ($args) {
            $q->where($args);
        });
    }

    /**
     * Scope a note by the patient's practice.
     *
     * @param $builder
     * @param $practiceId
     */
    public function scopePatientPractice($builder, $practiceId)
    {
        $builder->whereHas('patient', function ($q) use ($practiceId) {
            $q->where('program_id', '=', $practiceId);
        });
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param null $scale
     * @param bool $renderHtml
     */
    public function toPdf($scale = null, $renderHtml = false): string
    {
        $fileName = Carbon::today()->toDateString().'-'.$this->patient->id.'.pdf';
        $filePath = storage_path('pdfs/notes/'.$fileName);

        if (file_exists($filePath) && ! $renderHtml) {
            return $filePath;
        }

        $problems = $this->patient
            ->ccdProblems
            ->where('is_monitored', true)
            ->pluck('name')
            ->all();

        if ($renderHtml) {
            return view('pdfs.note', [
                'patient'  => $this->patient,
                'problems' => $problems,
                'sender'   => $this->author,
                'note'     => $this,
                'provider' => $this->patient->billingProviderUser(),
            ]);
        }

        $pdf = app('snappy.pdf.wrapper');
        $pdf->loadView('pdfs.note', [
            'patient'  => $this->patient,
            'problems' => $problems,
            'sender'   => $this->author,
            'note'     => $this,
            'provider' => $this->patient->billingProviderUser(),
        ]);

        $fontSize = null;

        if (optional($this->patient)
            ->primaryPractice) {
            $fontSize = $this->patient
                ->primaryPractice
                ->cpmSettings()
                ->note_font_size;
        }

        if ( ! empty($scale)) {
            $pdf->setOption('zoom', $scale);
        } elseif ( ! empty($fontSize)) {
            $pdf->setOption('zoom', $fontSize);
        }

        $pdf->save($filePath, true);

        return $filePath;
    }
}

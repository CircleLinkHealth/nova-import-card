<?php

namespace App;

use App\Contracts\PdfReport;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\NoteForwarded;
use App\Traits\IsAddendumable;
use App\Traits\PdfReportTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * App\Note
 *
 * @property int $id
 * @property int $patient_id
 * @property int $author_id
 * @property string $body
 * @property int $isTCM
 * @property int $did_medication_recon
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $type
 * @property string $performed_at
 * @property int|null $logger_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Addendum[] $addendums
 * @property-read \App\User $author
 * @property-read \App\Call $call
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\MailLog[] $mail
 * @property-read \App\User $patient
 * @property-read \App\User $program
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereDidMedicationRecon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereIsTCM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereLoggerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note wherePerformedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Note extends \App\BaseModel implements PdfReport
{
    use IsAddendumable,
        PdfReportTrait;

    protected $table = 'notes';

    protected $dates = [
        'performed_at',
    ];

    protected $fillable = [
        'patient_id',
        'author_id',
        'logger_id',
        'body',
        'isTCM',
        'type',
        'did_medication_recon',
        'performed_at',
    ];

    public function link()
    {
        return route('patient.note.view', [
            'patientId' => $this->patient_id,
            'noteId'    => $this->id,
        ]);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logger_id')->withTrashed();
    }

    public function mail()
    {
        return $this->hasMany('App\MailLog');
    }

    public function call()
    {
        return $this->hasOne('App\Call');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    public function program()
    {
        return $this->belongsTo('App\User', 'author_id', 'id');
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @return string
     */
    public function toPdf(): string
    {
        $problems = $this->patient
            ->cpmProblems
            ->pluck('name')
            ->all();

        $pdf = app('snappy.pdf.wrapper');
        $pdf->loadView('pdfs.note', [
            'patient'  => $this->patient,
            'problems' => $problems,
            'sender'   => $this->author,
            'note'     => $this,
            'provider' => $this->patient->billingProviderUser(),
        ]);

        $this->fileName = Carbon::now()->toDateString() . '-' . $this->patient->fullName . '.pdf';
        $filePath       = base_path('storage/pdfs/notes/' . $this->fileName);
        $pdf->save($filePath, true);

        return $filePath;
    }

    /**
     * Scope for notes that were forwarded
     *
     * @param $builder
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param bool $excludePatientSupport
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
                    ['notifiable_id', '!=', 948], //exclude patient support
                ]);
            }
        });
    }

    /**
     * Scope for notes that were forwarded to a specific notifiable
     *
     * @param $builder
     * @param $notifiableType
     * @param $notifiableId
     * @param Carbon|null $from
     * @param Carbon|null $to
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
     * Scope for notes that were emergencies (or not if you pass $yes = false)
     *
     * @param $builder
     * @param bool $yes
     */
    public function scopeEmergency($builder, bool $yes = true)
    {
        $builder->where('isTCM', '=', $yes);
    }

    /**
     * Returns the notifications that included this note as an attachment
     *
     * @return MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'attachment')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Forwards note to CareTeam and/or Support
     *
     * @param bool $notifySupport
     * @param bool $notifyCareteam
     */
    public function forward(bool $notifyCareteam = null, bool $notifySupport = null)
    {
        $this->load([
            'patient.primaryPractice.settings',
            'patient.patientInfo.location',
        ]);

        $recipients = collect();

        $cpmSettings = $this->patient->primaryPractice->cpmSettings();

        if ($notifyCareteam && $cpmSettings->email_note_was_forwarded) {
            $recipients = $this->patient->care_team_receives_alerts;
        }

        if ($notifySupport) {
            $recipients->push(User::find(948));
        }

        $recipients->map(function ($carePersonUser) {
            optional($carePersonUser)->notify(new NoteForwarded($this, ['mail']));
        });

        $channels = [];

        if ($cpmSettings->efax_pdf_notes) {
            $channels[] = FaxChannel::class;
        }

        if ($cpmSettings->dm_pdf_notes) {
            $channels[] = DirectMailChannel::class;
        }

        if ( ! $notifyCareteam || empty($channels)) {
            return;
        }

        optional($this->patient->patientInfo->location)->notify(new NoteForwarded($this, $channels));
    }

    /**
     * Scope a note by the patient's practice
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
}

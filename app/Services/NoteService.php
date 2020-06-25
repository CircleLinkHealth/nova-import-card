<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Call;
use App\CareplanAssessment;
use App\Filters\NoteFilters;
use App\Note;
use App\Notifications\SendPatientEmail;
use App\Repositories\CareplanAssessmentRepository;
use App\Repositories\NoteRepository;
use App\View\MetaTag;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NoteService
{
    private $assessmentRepo;
    private $noteRepo;
    private $userRepo;

    public function __construct(
        NoteRepository $noteRepo,
        UserRepository $userRepo,
        CareplanAssessmentRepository $assessmentRepo
    ) {
        $this->noteRepo       = $noteRepo;
        $this->userRepo       = $userRepo;
        $this->assessmentRepo = $assessmentRepo;
    }

    public function add($userId, $authorId, $body, $type, $isTCM, $did_medication_recon)
    {
        if ($userId && $authorId && ($body || 'Biometrics' == $type)) {
            if ( ! $this->userRepo->exists($userId)) {
                throw new \Exception('user with id "'.$userId.'" does not exist');
            }
            if ('Biometrics' != $type && ! $this->userRepo->exists($authorId)) {
                throw new \Exception('user with id "'.$authorId.'" does not exist');
            }
            if ('Biometrics' != $type) {
                $note                       = new Note();
                $note->patient_id           = $userId;
                $note->author_id            = $authorId;
                $note->body                 = $body;
                $note->type                 = $type;
                $note->isTCM                = $isTCM;
                $note->did_medication_recon = $did_medication_recon;

                return $this->noteRepo->add($note);
            }

            return $this->createNoteFromAssessment($this->assessmentRepo->editKeyTreatment(
                $userId,
                $authorId,
                $body
            ));
        }
        throw new \Exception('invalid parameters');
    }

    public function createAssessmentNote(CareplanAssessment $assessment)
    {
        $note             = new Note();
        $note->patient_id = $assessment->careplan_id;
        $note->author_id  = $assessment->provider_approver_id;

        $patient = User::find($note->patient_id);

        $note->body = 'Created/Edited Assessment for '.$patient->name().' ('.$assessment->careplan_id.') ... See '.
                              URL::to('/manage-patients/'.$assessment->careplan_id.'/view-careplan/assessment');
        $note->type         = 'Edit Assessment';
        $note->performed_at = Carbon::now();
        $note->save();
        $note->forward(true, true);

        return $note;
    }

    public function createNoteFromAssessment($assessment)
    {
        if ($assessment) {
            $note                       = new Note();
            $note->body                 = $assessment->key_treatment;
            $note->author_id            = $assessment->provider_approver_id;
            $note->patient_id           = $assessment->careplan_id;
            $note->created_at           = $assessment->created_at;
            $note->updated_at           = $assessment->updated_at;
            $note->isTCM                = 0;
            $note->did_medication_recon = 0;
            $note->type                 = 'Biometrics';
            $note->id                   = 0;

            return $note;
        }

        return null;
    }

    public function editNote(Note $note, $requestInput): Note
    {
        if ( ! empty($requestInput['status'])) {
            $note->status = $requestInput['status'];
        }

        if (empty($requestInput['type'])) {
            $requestInput['type'] = $note->type;
        }

        if (empty($requestInput['logger_id'])) {
            $requestInput['logger_id'] = auth()->id();
        }

        $note->logger_id = $requestInput['logger_id'];
        $note->isTCM     = isset($requestInput['tcm'])
            ? 'true' === $requestInput['tcm']
            : 0;
        $note->type    = $requestInput['type'];
        $note->summary = $requestInput['summary'] ?? null;
        $note->body    = $requestInput['body'];

        // this is no longer available in create note page
        $note->performed_at = Carbon::now(); //$requestInput['performed_at'];

        $note->did_medication_recon = isset($requestInput['medication_recon'])
            ? 'true' === $requestInput['medication_recon']
            : 0;
        $note->success_story = isset($requestInput['success_story'])
            ? 'true' === $requestInput['success_story']
            : 0;

        if ($note->isDirty()) {
            $note->save();
        }

        return $note;
    }

    public function editPatientNote(
        $id,
        $userId,
        $authorId,
        $body,
        $isTCM,
        $did_medication_recon,
        $type = null,
        $summary = null
    ) {
        if ( ! $type) {
            if ( ! $id) {
                throw new \Exception('$id is required');
            }
            $note = Note::find($id);
            if ($note->patient_id != $userId) {
                throw new \Exception('Note with id "'.$id.'" does not belong to patient with id "'.$userId.'"');
            }
            if ($note->author_id != $authorId) {
                throw new \Exception('Attempt to edit note blocked because note does not belong to author');
            }
            $note                       = new Note();
            $note->id                   = $id;
            $note->patient_id           = $userId;
            $note->author_id            = $authorId;
            $note->body                 = $body;
            $note->isTCM                = $isTCM;
            $note->did_medication_recon = $did_medication_recon;

            if ($summary) {
                $note->summary = $summary;
            }

            return $this->noteRepo->edit($note);
        }

        return $this->createNoteFromAssessment($this->assessmentRepo->editKeyTreatment($userId, $authorId, $body));
    }

    /**
     * Forward the note.
     *
     * Force forwards to CareTeam if the patient's in the hospital, ie `if(true === note->isTCM)`
     *
     * @param bool $notifyCareTeam
     * @param bool $notifyCLH
     * @param bool $forceNotify
     *
     * @return void
     */
    public function forwardNoteIfYouMust(Note $note, $notifyCareTeam = false, $notifyCLH = false, $forceNotify = false)
    {
        if ($note->isTCM && $this->practiceHasNotesNotificationsEnabled($note->patient->primaryPractice)) {
            $notifyCareTeam = $forceNotify = true;
        }

        return $note->forward($notifyCareTeam, $notifyCLH, $forceNotify);
    }

    public function getAllForwardedNotesWithRange(
        Carbon $start,
        Carbon $end
    ) {
        $patients = User::ofType('participant')
            ->get()
            ->pluck('id');

        $notes = Note::whereIn('patient_id', $patients)
            ->whereBetween('created_at', [
                $start,
                $end,
            ])
            ->has('notifications')
            ->orderBy('performed_at', 'desc')
            ->with(['patient', 'call', 'notifications', 'author'])
            ->get();

        $provider_forwarded_notes = [];

        foreach ($notes as $note) {
            if ($this->wasForwardedToCareTeam($note)) {
                $provider_forwarded_notes[] = $note;
            }
        }

        return collect($provider_forwarded_notes);
    }

    public function getForwardedNotesWithRangeForPatients(
        $patients,
        $start,
        $end
    ) {
        return Note::whereIn('patient_id', $patients)
            ->whereBetween('performed_at', [
                $start,
                $end,
            ])
            ->has('notifications')
            ->orderBy('performed_at', 'desc')
            ->with(['patient', 'call', 'notifications', 'author'])
            ->get();
    }

    public function getForwardedNotesWithRangeForProvider(
        $providers,
        $start,
        $end
    ) {
        $patients = User::whereHas('careTeamMembers', function ($q) use (
            $providers
        ) {
            $q->whereIn('member_user_id', $providers)
                ->where('type', 'billing_provider');
        })->pluck('id');

        $notes = $this->getForwardedNotesWithRangeForPatients($patients, $start, $end);

        $provider_forwarded_notes = [];

        foreach ($notes as $note) {
            if ($this->wasForwardedToCareTeam($note)) {
                $provider_forwarded_notes[] = $note;
            }
        }

        return collect($provider_forwarded_notes);
    }

    public function getForwards(Note $note)
    {
        return $note->notifications()
            ->hasNotifiableType(User::class)
            ->with('notifiable')
            ->get()
            ->mapWithKeys(function ($notification) {
                if ( ! $notification->notifiable) {
                    return ['N/A' => $notification->created_at->format('m/d/y h:iA T')];
                }

                return [$notification->notifiable->getFullName() => $notification->created_at->format('m/d/y h:iA T')];
            });
    }

    public function getNoteEmails(Note $note)
    {
        return $note->patient->notifications()->where('type', SendPatientEmail::class)->where(
            'data->note_id',
            $note->id
        )->get()->map(function ($n) {
            $data = $n->data;

            if (isset($data['sender_id'])) {
                $sender = User::find($n->data['sender_id']);
                $email['senderFullName'] = $sender
                    ? $sender->getFullName()
                    : 'N/A';
            }

            if (isset($data['email_content'])) {
                $email['content'] = $data['email_content']
                    ?: 'No content found';
            }
            $email['subject'] = $data['email_subject'];

            $email['created_at'] = presentDate($n->created_at);

            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $a['id'] = $attachment['media_id'];
                    $media = Media::where('collection_name', 'patient-email-attachments')
                        ->where('model_id', $n->notifiable_id)
                        ->whereIn(
                            'model_type',
                            [\App\User::class, 'CircleLinkHealth\Customer\Entities\User']
                        )
                        ->find($attachment['media_id']);

                    $a['url'] = $media->getUrl();
                    $a['file_name'] = $media->file_name;
                    $a['is_image'] = Str::contains($media->mime_type, 'image')
                        ?: false;

                    $email['attachments'][] = $a;
                }
            }

            return $email;
        });
    }

    //Get all notes for patients with specified date range

//    public function getNotesAndOfflineActivitiesForPatient(User $patient)
//    {
//        $notes = $patient->notes;
//        $activities = $patient->activities;
//        $appointments = $patient->appointments;
//
//        return $notes->merge($activities)
//            ->merge($appointments);
//    }

    //Get all notes that have been sent to anyone for a given provider with specified date range

    public function getNotesWithRangeForPatients(
        $patients,
        $start,
        $end
    ) {
        return Note::whereIn('patient_id', $patients)
            ->whereBetween('performed_at', [
                $start,
                $end,
            ])
            ->orderBy('performed_at', 'desc')
            ->with('patient')->with(['call', 'notifications', 'author'])
            ->get();
    }

    //Save call information for note

    public function getNotesWithRangeForProvider(
        $providers,
        $start,
        $end
    ) {
        $patients = User::whereHas(
            'careTeamMembers',
            function ($q) use (
                $providers
            ) {
                $q->whereIn('member_user_id', $providers)
                    ->where('type', 'billing_provider');
            }
        )->pluck('id');

        return $this->getNotesWithRangeForPatients($patients, $start, $end);
    }

    //return bool of whether note was sent to a provider

    public function getSeenForwards(Note $note)
    {
        return $note->notifications()
            ->hasNotifiableType(User::class)
            ->with('notifiable')
            ->whereNotNull('read_at')
            ->get()
            ->mapWithKeys(function ($notification) {
                return [$notification->notifiable->getFullName() => $notification->read_at->format('m/d/y h:iA T')];
            });
    }

    public function getUserDraftNotes($userId)
    {
        return Note::where('status', '=', 'draft')
            ->where('author_id', '=', $userId)
            ->get();
    }

    public function markNoteAsRead(
        User $viewer,
        Note $note
    ) {
        $viewer->unreadNotifications()
            ->hasNotifiableType(User::class)
            ->hasAttachmentType(Note::class)
            ->where('attachment_id', '=', $note->id)
            ->get()
            ->markAsRead();
    }

    public function patientNotes($userId, NoteFilters $filters)
    {
        return $this->noteRepo->patientNotes($userId, $filters);
    }

    public function practiceHasNotesNotificationsEnabled(Practice $practice): bool
    {
        return with($practice->cpmSettings(), function ($settings) {
            return $settings->email_note_was_forwarded || $settings->efax_pdf_notes || $settings->dm_pdf_notes;
        });
    }

    public function storeCallForNote(
        $note,
        $status,
        User $patient,
        User $author,
        $phone_direction,
        $scheduler,
        $attemptNote = ''
    ) {
        if ('inbound' == $phone_direction) {
            $outbound_num  = $patient->getPrimaryPhone();
            $outbound_id   = $patient->id;
            $inbound_num   = $author->getPrimaryPhone();
            $inbound_id    = $author->id;
            $isCpmOutbound = false;
        } else {
            $outbound_num  = $author->getPrimaryPhone();
            $outbound_id   = $author->id;
            $inbound_num   = $patient->getPrimaryPhone();
            $inbound_id    = $patient->id;
            $isCpmOutbound = true;
        }

        return Call::create([
            'note_id' => $note->id,
            'service' => 'phone',
            'status'  => $status,

            'scheduler' => $scheduler,

            'attempt_note' => $attemptNote,

            'inbound_phone_number'  => $outbound_num,
            'outbound_phone_number' => $inbound_num,

            'inbound_cpm_id'  => $inbound_id,
            'outbound_cpm_id' => $outbound_id,

            'called_date' => $note->performed_at->toDateTimeString(),

            'call_time'  => 0,
            'created_at' => $note->performed_at,

            'is_cpm_outbound' => $isCpmOutbound,
        ]);
    }

    public function tags(Note $note)
    {
        $meta_tags = [];

        if ($note->call) {
            if ($note->call->is_cpm_inbound) {
                $meta_tags[] = new MetaTag('info', 'Inbound Call');
            } else {
                $meta_tags[] = new MetaTag('info', 'Outbound Call');
            }

            if ('reached' == $note->call->status) {
                $meta_tags[] = new MetaTag('info', 'Successful Clinical Call');
            }
        }

        $forwardedTo = $this->getForwards($note);

        if ($forwardedTo->count() > 0) {
            $meta_tags[] = new MetaTag('info', 'Forwarded', $forwardedTo->keys()->implode(', '));
        }

        if ($note->isTCM) {
            $meta_tags[] = new MetaTag('danger', 'Patient Recently in Hospital/ER');
        }

        if ($note->did_medication_recon) {
            $meta_tags[] = new MetaTag('info', 'Medication Reconciliation');
        }

        return $meta_tags;
    }

    public function wasForwardedToCareTeam(Note $note)
    {
        return $note->notifications()
            ->where('notifiable_id', '!=', 948)
            ->exists();
    }

    public function wasSeenByBillingProvider(Note $note)
    {
        $notifiableId = optional($note->patient->billingProviderUser())->id;

        return $notifiableId
            ? $note->notifications()
                ->hasNotifiableType(User::class)
                ->where([
                    ['notifiable_id', '=', $notifiableId],
                ])
                ->whereNotNull('read_at')
                ->exists()
            : null;
    }
}

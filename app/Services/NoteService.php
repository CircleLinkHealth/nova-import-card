<?php

namespace App\Services;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Call;
use App\CarePerson;
use App\CareplanAssessment;
use App\CLH\Repositories\UserRepository;
use App\Filters\NoteFilters;
use App\Note;
use App\Patient;
use App\PatientMonthlySummary;
use App\Repositories\CareplanAssessmentRepository;
use App\Repositories\NoteRepository;
use App\User;
use App\View\MetaTag;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\URL;

class NoteService
{
    private $noteRepo;
    private $userRepo;
    private $assessmentRepo;

    public function __construct(NoteRepository $noteRepo, UserRepository $userRepo, CareplanAssessmentRepository $assessmentRepo) {
        $this->noteRepo = $noteRepo;
        $this->userRepo = $userRepo;
        $this->assessmentRepo = $assessmentRepo;
    }

    public function repo() {
        return $this->noteRepo;
    }

    public function patientNotes($userId, NoteFilters $filters) {
        return $this->repo()->patientNotes($userId, $filters);
    }

    public function patientBiometricNotes($userId) {
        $assessments = $this->assessmentRepo->assessments($userId)->where('key_treatment', '!=', 'null');
        return $assessments->map([$this, 'createNoteFromAssessment']);
    }

    function createNoteFromAssessment($assessment) {
        if ($assessment) {
            $note = new Note();
            $note->body = $assessment->key_treatment;
            $note->author_id = $assessment->provider_approver_id;
            $note->patient_id = $assessment->careplan_id;
            $note->created_at = $assessment->created_at;
            $note->updated_at = $assessment->updated_at;
            $note->isTCM = 0;
            $note->did_medication_recon = 0;
            $note->type = 'Biometrics';
            $note->id = 0;
            return $note;
        }
        else return null;
    }

    public function add($userId, $authorId, $body, $type, $isTCM, $did_medication_recon) {
        if ($userId && $authorId && ($body || $type == 'Biometrics')) {
            if (!$this->userRepo->exists($userId)) {
                throw new Exception('user with id "' . $userId . '" does not exist');
            }
            else if ($type != 'Biometrics' && !$this->userRepo->exists($authorId)) {
                throw new Exception('user with id "' . $authorId . '" does not exist');
            }
            else {
                if ($type != 'Biometrics') {
                    $note = new Note();
                    $note->patient_id = $userId;
                    $note->author_id = $authorId;
                    $note->body = $body;
                    $note->type = $type;
                    $note->isTCM = $isTCM;
                    $note->did_medication_recon = $did_medication_recon;
                    return $this->repo()->add($note);
                }
                else {
                    return $this->createNoteFromAssessment($this->assessmentRepo->editKeyTreatment($userId, $authorId, $body));
                }
            }
        }
        else throw new Exception('invalid parameters');
    }

    public function editPatientNote($id, $userId, $authorId, $body, $isTCM, $did_medication_recon, $type = null) {
        if (!$type) {
            if (!$id) throw new Exception('$id is required');
            else {
                $note = $this->repo()->model()->find($id);
                if ($note->patient_id != $userId) throw new Exception('Note with id "' . $id . '" does not belong to patient with id "' . $userId . '"');
                else if ($note->author_id != $authorId) throw new Exception('Attempt to edit note blocked because note does not belong to author');
                else {
                    $note = new Note();
                    $note->id = $id;
                    $note->patient_id = $userId;
                    $note->author_id = $authorId;
                    $note->body = $body;
                    $note->isTCM = $isTCM;
                    $note->did_medication_recon = $did_medication_recon;
                    return $this->repo()->edit($note);
                }
            }
        }
        else {
            return $this->createNoteFromAssessment($this->assessmentRepo->editKeyTreatment($userId, $authorId, $body));
        }
    }

    public function storeNote($input)
    {
        $note = Note::create($input);

        $notifyCareTeam = $input['notify_careteam'] ?? false;
        $notifyCLH      = $input['notify_circlelink_support'] ?? false;

        if ($input['tcm'] == 'true') {
            $note->isTCM    = true;
            $notifyCareTeam = true;
        } else {
            $note->isTCM = false;
        }

        if (isset($input['medication_recon'])) {
            $note->did_medication_recon = true;
        } else {
            $note->did_medication_recon = false;
        }

        $note->save();

        $note->forward($notifyCareTeam, $notifyCLH);

        return $note;
    }

    public function createAssessmentNote(CareplanAssessment $assessment) {
        $note = new Note();
        $note->patient_id = $assessment->careplan_id;
        $note->author_id = $assessment->provider_approver_id;

        $patient = User::find($note->patient_id);

        $note->body = 'Created/Edited Assessment for ' . $patient->name() . ' (' . $assessment->careplan_id . ') ... See ' . 
                        URL::to('/manage-patients/' . $assessment->careplan_id . '/view-careplan/assessment');
        $note->type = 'Edit Assessment';
        $note->performed_at = Carbon::now();
        $note->save();
        $note->forward(true, true);
        return $note;
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

    public function getNotesWithRangeForProvider(
        $provider,
        $start,
        $end
    ) {

        $patients = User::whereHas(
            'careTeamMembers',
            function ($q) use (
                $provider
            ) {

                $q->where('member_user_id', $provider)
                  ->where('type', 'billing_provider');
            }
        )->pluck('id');

        return $this->getNotesWithRangeForPatients($patients, $start, $end);
    }

    //Save call information for note

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

    public function getForwardedNotesWithRangeForProvider(
        $provider,
        $start,
        $end
    ) {

        $patients = User::whereHas('careTeamMembers', function ($q) use (
            $provider
        ) {
            $q->where('member_user_id', $provider)
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

    public function updatePatientRecords(
        Patient $patient,
        $ccmComplex
    ) {

        $date_index = Carbon::now()->firstOfMonth()->toDateString();

        $patientRecord = PatientMonthlySummary::where('patient_id', $patient->user_id)
            ->where('month_year', $date_index)
            ->first();

        if (empty($patientRecord)) {
            $patientRecord = PatientMonthlySummary::updateCCMInfoForPatient(
                $patient->user_id,
                $patient->cur_month_activity_time
            );
        } else {
            $patientRecord->is_ccm_complex = 0;
            $patientRecord->save();
        }

        if ($ccmComplex) {
            $patientRecord->is_ccm_complex = 1;
            $patientRecord->save();

            if ($patient->cur_month_activity_time > 3600 && auth()->user()->nurseInfo) {
                (new AlternativeCareTimePayableCalculator(auth()->user()->nurseInfo))->adjustPayOnCCMComplexSwitch60Mins();
            }
        }

        return $patientRecord;
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

        if ($phone_direction == 'inbound') {
            $outbound_num  = $patient->primaryPhone;
            $outbound_id   = $patient->id;
            $inbound_num   = $author->primaryPhone;
            $inbound_id    = $author->id;
            $isCpmOutbound = false;
        } else {
            $outbound_num  = $author->primaryPhone;
            $outbound_id   = $author->id;
            $inbound_num   = $patient->primaryPhone;
            $inbound_id    = $patient->id;
            $isCpmOutbound = true;
        }

        Call::create([

            'note_id' => $note->id,
            'service' => 'phone',
            'status'  => $status,

            'scheduler' => $scheduler,

            'attempt_note' => $attemptNote,

            'inbound_phone_number'  => $outbound_num,
            'outbound_phone_number' => $inbound_num,

            'inbound_cpm_id'  => $inbound_id,
            'outbound_cpm_id' => $outbound_id,

            //@todo figure out call times!
            'called_date'     => Carbon::now()->toDateTimeString(),

            'call_time'  => 0,
            'created_at' => $note->performed_at,

            'is_cpm_outbound' => $isCpmOutbound,

        ]);
    }

    //return bool of whether note was sent to a provider

    public function getPatientCareTeamMembers($patientId)
    {

        $careteam_info = [];
        $careteam_ids  = CarePerson
            ::whereUserId($patientId)->pluck('member_user_id');

        foreach ($careteam_ids as $id) {
            if (User::find($id)) {
                $careteam_info[$id] = User::find($id)->fullName;
            }
        }

        return $careteam_info;
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

            if ($note->call->status == 'reached') {
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

    public function getSeenForwards(Note $note)
    {
        return $note->notifications()
                    ->hasNotifiableType(User::class)
                    ->with('notifiable')
                    ->whereNotNull('read_at')
                    ->get()
                    ->mapWithKeys(function ($notification) {
                        return [$notification->notifiable->fullName => $notification->read_at->format('m/d/y h:iA T')];
                    });
    }

    public function getForwards(Note $note)
    {
        return $note->notifications()
                    ->hasNotifiableType(User::class)
                    ->with('notifiable')
                    ->get()
                    ->mapWithKeys(function ($notification) {
                        if (!$notification->notifiable) return ['N/A' => $notification->created_at->format('m/d/y h:iA T')];

                        return [$notification->notifiable->fullName => $notification->created_at->format('m/d/y h:iA T')];
                    });
    }

    public function wasForwardedToCareTeam(Note $note)
    {
        return $note->notifications()
                    ->where('notifiable_id', '!=', 948)
                    ->count() > 0;
    }

    public function wasSeenByBillingProvider(Note $note)
    {
        return $note->notifications()
                    ->hasNotifiableType(User::class)
                    ->where([
                        ['read_at', '!=', null],
                        ['notifiable_id', '=', $note->patient->billingProviderUser()->id],
                    ])
                    ->count() > 0;
    }
}

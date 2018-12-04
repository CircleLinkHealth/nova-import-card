<?php namespace App\Formatters;

use App\Activity;
use App\Appointment;
use App\Contracts\ReportFormatter;
use App\Models\CCD\Allergy;
use App\Models\CCD\Medication;
use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmMisc;
use App\Services\CPM\CpmMiscService;
use App\Services\NoteService;
use App\Services\ReportsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class WebixFormatter implements ReportFormatter
{
    private $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function formatDataForNotesListingReport($notes, $request)
    {
        $count = 0;

        $formatted_notes = [];

        foreach ($notes as $note) {
            $formatted_notes[$count]['id'] = $note->id;

            //Display Name
            $formatted_notes[$count]['patient_name'] = $note->patient->display_name
                ? $note->patient->display_name
                : '';
            //id
            $formatted_notes[$count]['patient_id'] = $note->patient_id;

            $formatted_notes[$count]['program_name'] = $note->patient->primaryPractice->display_name;

            //Provider Name
            $provider = User::find(intval($note->patient->billingProviderID));
            if (is_object($provider)) {
                $formatted_notes[$count]['provider_name'] = $provider->getFullName();
            } else {
                $formatted_notes[$count]['provider_name'] = '';
            }

            //Author
            $author = $note->author;
            if (is_object($author)) {
                $formatted_notes[$count]['author_name'] = $author->display_name;
            } else {
                $formatted_notes[$count]['author_name'] = '';
            }

            //Type
            $formatted_notes[$count]['type'] = $note->type;

            //Body
            $formatted_notes[$count]['comment'] = $note->body;

            $formatted_notes[$count]['date'] = Carbon::parse($note->performed_at)->format('Y-m-d');

            //TAGS
            $formatted_notes[$count]['tags'] = '';


            if ($this->noteService->wasForwardedToCareTeam($note)) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
            }

            if ($note->call && $note->call->status == 'reached') {
                $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
            }

            if ($note->isTCM) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $was_seen = $this->noteService->wasSeenByBillingProvider($note);

            if ($was_seen) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-success"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></div> ';
            }

            $count++;
        }

        return $formatted_notes;
    }

    public function formatDataForNotesAndOfflineActivitiesReport($patient)
    {
        $formatted_data = collect();
        $count          = 0;

        $task_types      = Activity::task_types_to_topics();
        $billingProvider = $patient->getBillingProviderName();

        $notes = $patient->notes->sortByDesc('id')->map(function ($note) use ($patient, $billingProvider) {
            $result = [
                'id'            => $note->id,
                'logger_name'   => $note->author->getFullName(),
                'comment'       => $note->body,
                'logged_from'   => 'note',
                'type_name'     => $note->type,
                'performed_at'  => $note->performed_at->toDateString(),
                'provider_name' => $billingProvider,
                'tags'          => '',
            ];

            //pangratios: add support for task types
            if ($note->call && $note->call->type === 'task') {
                $result['logged_from'] = 'note_task';
            }

            if ($note->notifications->count() > 0) {
                if ($this->noteService->wasForwardedToCareTeam($note)) {
                    $result['tags'] .= '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
                }
            }

            if ($note->call && $note->call->status == 'reached') {
                $result['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
            }

            if ($note->isTCM) {
                $result['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $was_seen = $this->noteService->wasSeenByBillingProvider($note);

            if ($was_seen) {
                $result['tags'] .= '<div class="label label-success"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></div> ';
            }

            return $result;
        });

        if ($notes->isEmpty()) {
            $notes = collect([]);
        }

        $appointments = $patient->appointments->map(function ($appointment) use ($billingProvider) {
            return [
                'id'            => $appointment->id,
                'logger_name'   => optional($appointment->author)->getFullName(),
                'comment'       => $appointment->comment,
                'logged_from'   => 'appointment',
                'type_name'     => $appointment->type,
                'performed_at'  => Carbon::parse($appointment->date)->toDateString(),
                'provider_name' => $billingProvider,
                'tags'          => '',
            ];
        });

        if ($appointments->isEmpty()) {
            $appointments = collect([]);
        }

        $activities = $patient->activities->map(function ($activity) use ($billingProvider) {
            return [
                'id'            => $activity->id,
                'logger_name'   => $activity->provider->getFullName(),
                'comment'       => $activity->getCommentForActivity(),
                'logged_from'   => 'manual_input',
                'type_name'     => $activity->type,
                'performed_at'  => $activity->performed_at,
                'provider_name' => $billingProvider,
                'tags'          => '',
            ];
        });

        if ($activities->isEmpty()) {
            $activities = collect([]);
        }

        $report_data = $notes->merge($appointments)
                             ->merge($activities)
                             ->sortByDesc('performed_at')
                             ->values()
                             ->toJson();

        if (! empty($report_data)) {
            return $report_data;
        }

        return '';
    }

    public function formatDataForViewPrintCareplanReport($users)
    {
        $careplanReport    = [];
        $cpmProblemService = (new \App\Services\CPM\CpmProblemService(
            new \App\Repositories\CpmProblemRepository(app()),
            new \App\Repositories\UserRepositoryEloquent(app())
        ));

        foreach ($users as $user) {
            $careplanReport[$user->id]['symptoms']    = $user->cpmSymptoms()->get()->pluck('name')->all();
            $careplanReport[$user->id]['problem']     = $user->cpmProblems()->get()->sortBy('name')->pluck('name')->all();
            $careplanReport[$user->id]['problems']    = $cpmProblemService->getProblemsWithInstructionsForUser($user);
            $careplanReport[$user->id]['lifestyle']   = $user->cpmLifestyles()->get()->pluck('name')->all();
            $careplanReport[$user->id]['biometrics']  = $user->cpmBiometrics()->get()->pluck('name')->all();
            $careplanReport[$user->id]['medications'] = $user->cpmMedicationGroups()->get()->pluck('name')->all();
        }

        $other_problems = (new ReportsService())->getInstructionsforOtherProblems($user);

        if (! empty($other_problems) && isset($careplanReport[$user->id]) && isset($careplanReport[$user->id]['problems'])) {
            if (! is_string($careplanReport[$user->id]['problems'])) {
                $careplanReport[$user->id]['problems']['Full Conditions List'] = $other_problems;
            }
        }

        //Get Biometrics with Values
        $careplanReport[$user->id]['bio_data'] = [];

        //Ignore Smoking - Untracked Biometric
        if (($key = array_search(CpmBiometric::SMOKING, $careplanReport[$user->id]['biometrics'])) !== false) {
            unset($careplanReport[$user->id]['biometrics'][$key]);
        }

        foreach ($careplanReport[$user->id]['biometrics'] as $metric) {
            $biometric        = $user->cpmBiometrics->where('name', $metric)->first();
            $biometric_values = app(config('cpmmodelsmap.biometrics')[$biometric->type])->getUserValues($user);

            if ($biometric_values) {
                //Check to see whether the user has a starting value
                if ($biometric_values['starting'] == '') {
                    $biometric_values['starting'] = 'N/A';
                }

                //Check to see whether the user has a target value
                if ($biometric_values['target'] == '') {
                    $biometric_values['target'] = 'TBD';
                }

                //If no values are retrievable, then default to these:
            } else {
                $biometric_values['starting'] = 'N/A';
                $biometric_values['target']   = 'TBD';
            }

            //Special verb use for each biometric
            if ($metric == 'Blood Pressure') {
                if ($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {
                    $biometric_values['verb'] = 'Regulate';
                } else {
                    $starting = explode('/', $biometric_values['starting']);
                    $starting = $starting[0];
                    $target   = explode('/', $biometric_values['target']);
                    $target   = $target[0];

                    if ($starting > $target) {
                        $biometric_values['verb'] = 'Decrease';
                    } else {
                        if ($starting < 90) {
                            $biometric_values['verb'] = 'Increase';
                        } else {
                            $biometric_values['verb'] = 'Regulate';
                        }
                    }
                }
            }

            if ($metric == 'Weight') {
                if ($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {
                    $biometric_values['verb'] = 'Regulate';
                } else {
                    if ($biometric_values['starting'] > $biometric_values['target']) {
                        $biometric_values['verb'] = 'Decrease';
                    } else {
                        if ($biometric_values['starting'] < $biometric_values['target']) {
                            $biometric_values['verb'] = 'Increase';
                        } else {
                            $biometric_values['verb'] = 'Regulate';
                        }
                    }
                }
            }

            if ($metric == 'Blood Sugar') {
                if ($biometric_values['starting'] == 'N/A' || $biometric_values['target'] == 'TBD') {
                    $biometric_values['verb'] = 'Regulate';
                } else {
                    if ($biometric_values['starting'] > $biometric_values['target']) {
                        $biometric_values['verb'] = 'Decrease';
                    } else {
                        if ($biometric_values['starting'] < $biometric_values['target']) {
                            $biometric_values['verb'] = 'Increase';
                        } else {
                            $biometric_values['verb'] = 'Regulate';
                        }
                    }
                }

                if (intval($biometric_values['starting']) >= 70 && intval($biometric_values['starting']) <= 130) {
                    $biometric_values['verb'] = 'Regulate';
                }
            }


            $careplanReport[$user->id]['bio_data'][$metric]['target']   = $biometric_values['target'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->id]['bio_data'][$metric]['starting'] = $biometric_values['starting'] . ReportsService::biometricsUnitMapping($metric);
            $careplanReport[$user->id]['bio_data'][$metric]['verb']     = $biometric_values['verb'];
        }//dd($careplanReport[$user->id]['bio_data']);


        array_reverse($careplanReport[$user->id]['bio_data']);

        //Medications List
        $careplanReport[$user->id]['taking_meds'] = 'No instructions at this time';
        $medicationList                           = $user->cpmMiscs->where('name', CpmMisc::MEDICATION_LIST)->all();
        if (! empty($medicationList)) {
            $meds = Medication::where('patient_id', '=', $user->id)->orderBy('name')->get();
            if ($meds->count() > 0) {
                $i                                        = 0;
                $careplanReport[$user->id]['taking_meds'] = [];
                foreach ($meds as $med) {
                    empty($med->name)
                        ? $medText = ''
                        : $medText = '' . $med->name;

                    if (! empty($med->sig)) {
                        $medText .= '<br /><span style="font-style:italic;">- ' . $med->sig . '</span>';
                    }
                    $careplanReport[$user->id]['taking_meds'][] = $medText;
                    $i++;
                }
            }
        }

        //Allergies
        $careplanReport[$user->id]['allergies'] = 'No instructions at this time';

        $allergies = Allergy::where('patient_id', '=', $user->id)
                            ->orderBy('allergen_name')
                            ->get()
                            ->unique('allergen_name')
                            ->values();

        if ($allergies->count() > 0) {
            $careplanReport[$user->id]['allergies'] = '';
            $i                                      = 0;
            foreach ($allergies as $allergy) {
                if (empty($allergy->allergen_name)) {
                    continue 1;
                }
                if ($i > 0) {
                    $careplanReport[$user->id]['allergies'] .= '<br>';
                }
                $careplanReport[$user->id]['allergies'] .= $allergy->allergen_name;
                $i++;
            }
        }

        //Social Services
        if ($user->cpmMiscs->where('name', CpmMisc::SOCIAL_SERVICES)->first()) {
            $careplanReport[$user->id]['social'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::SOCIAL_SERVICES
            );
        } else {
            $careplanReport[$user->id]['social'] = '';
        }

        //Other
        if ($user->cpmMiscs->where('name', CpmMisc::OTHER)->first()) {
            $careplanReport[$user->id]['other'] = app(CpmMiscService::class)->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::OTHER
            );
        } else {
            $careplanReport[$user->id]['other'] = '';
        }

        $careplanReport[$user->id]['appointments'] = null;

        //Appointments
        //Upcoming
        $upcoming = Appointment
            ::wherePatientId($user->id)
            ->where('date', '>', Carbon::now()->toDateString())
            ->orderBy('date')
            ->take(3)->get();

        foreach ($upcoming as $appt) {
            $provider = User::find($appt->provider_id);

            $phone     = null;
            $specialty = null;
            if ($provider) {
                $specialty = $provider->getSpecialty() ?? null;
                if ($specialty) {
                    $specialty = '(' . $specialty . ')';
                }

                //format super specific phone number requirements
                if ($provider->getPrimaryPhone()) {
                    $phone = "P: " . preg_replace(
                            '~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~',
                            '$1-$2-$3',
                            $provider->getPrimaryPhone()
                        );
                }
            }

            $formattedUpcomingAppointment[$appt->id] = [

                'name'      => optional($provider)->getFullName(),
                'specialty' => $specialty,
                'date'      => $appt->date,
                'type'      => $appt->type,
                'time'      => Carbon::parse($appt->time)->format('H:i A') . ' ' . Carbon::parse($user->timezone)->format('T'),
                'address'   => optional($provider)->address
                    ? "A: $provider->address. "
                    : '',
                'phone'     => $phone,

            ];

            $careplanReport[$user->id]['appointments']['upcoming'] = $formattedUpcomingAppointment;
        }

        //past
        $past = Appointment
            ::wherePatientId($user->id)
            ->where('date', '<', Carbon::now()->toDateString())
            ->orderBy('date', 'desc')
            ->take(3)->get();

        foreach ($past as $appt) {
            $provider = User::find($appt->provider_id);

            if (! $provider) {
                continue;
            }

            $specialty = $provider->getSpecialty() ?? null;
            if ($specialty) {
                $specialty = '(' . $specialty . ')';
            }

            //format super specific phone number requirements
            if ($provider->getPrimaryPhone()) {
                $phone = "P: " . preg_replace(
                        '~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~',
                        '$1-$2-$3',
                        $provider->getPrimaryPhone()
                    );
            } else {
                $phone = null;
            }

            $formattedPastAppointment[$appt->id] = [

                'name'      => $provider->getFullName(),
                'specialty' => $specialty,
                'date'      => $appt->date,
                'type'      => $appt->type
                    ? "$appt->type,"
                    : '',
                'time'      => Carbon::parse($appt->time)->format('H:i A') . ' ' . Carbon::parse($user->timezone)->format('T'),
                'address'   => $provider->address
                    ? "A: $provider->address. "
                    : '',
                'phone'     => $phone,

            ];

            $careplanReport[$user->id]['appointments']['past'] = $formattedPastAppointment;
        }


//        array_reverse($biometrics)
        return $careplanReport;
    }

    public function patients(Collection $patients = null)
    {
        $patientData = [];
        $auth        = \Auth::user();

        if (! $patients) {
            $patients = $auth->patientList();
        }

        $foundUsers    = []; // save resources, no duplicate db calls
        $foundPrograms = []; // save resources, no duplicate db calls

        $canApproveCarePlans   = $auth->canApproveCareplans();
        $canQAApproveCarePlans = $auth->canQAApproveCarePlans();
        $isCareCenter          = $auth->hasRole('care-center');
        $isAdmin               = $auth->isAdmin();
        $isProvider            = $auth->hasRole('provider');
        $isPracticeStaff       = $auth->hasRole(['office_admin', 'med_assistant']);


        foreach ($patients as $patient) {
            // skip if patient has no name
            if (empty($patient->first_name)) {
                continue 1;
            }

            $careplanStatus     = $patient->carePlan->status ?? '';
            $careplanStatusLink = '';
            $approverName       = 'NA';
            $tooltip            = 'NA';

            if ($careplanStatus == 'provider_approved') {
                $approver = $patient->carePlan->providerApproverUser;
                if ($approver) {
                    $approverName = $approver->getFullName();
                }

                $carePlanProviderDate = $patient->carePlan->provider_date;
                $careplanStatus       = 'Approved';
                $careplanStatusLink   = '<span data-toggle="" title="' . $approverName . ' ' . $carePlanProviderDate . '">Approved</span>';
                $tooltip              = $approverName . ' ' . $carePlanProviderDate;
            } else {
                if ($careplanStatus == 'qa_approved') {
                    $careplanStatus     = 'Approve Now';
                    $tooltip            = $careplanStatus;
                    $careplanStatusLink = 'Approve Now';
                    if ($canApproveCarePlans) {
                        $careplanStatusLink = '<a style="text-decoration:underline;" href="' . route(
                                'patient.careplan.print',
                                ['patient' => $patient->id]
                            ) . '"><strong>Approve Now</strong></a>';
                    }
                } else {
                    if ($careplanStatus == 'draft') {
                        $careplanStatus     = 'CLH Approve';
                        $tooltip            = $careplanStatus;
                        $careplanStatusLink = 'CLH Approve';
                        if ($canQAApproveCarePlans) {
                            $careplanStatusLink = '<a style="text-decoration:underline;" href="' . route(
                                    'patient.demographics.show',
                                    ['patient' => $patient->id]
                                ) . '"><strong>CLH Approve</strong></a>';
                        }
                    }
                }
            }

            // get billing provider name
            $bpName = '';
            $bpID   = $patient->billingProviderID;
            if (! isset($foundPrograms[$patient->program_id])) {
                $program                             = $patient->primaryPractice;
                $foundPrograms[$patient->program_id] = $program;
            } else {
                $program = $foundPrograms[$patient->program_id];
            }
            $programName = $program->display_name;

            $bpCareTeamMember = $patient->careTeamMembers->first();

            if ($bpCareTeamMember) {
                $bpUser = $bpCareTeamMember->user;

                if (! $bpUser) {
                    continue;
                }

                $bpName            = $bpUser->getFullName();
                $foundUsers[$bpID] = $bpUser;
            }

            // get date of last observation
            $lastObservationDate = 'No Readings';
            $lastObservation     = $patient->observations;
            if ($lastObservation->count() > 0) {
                $lastObservationDate = date("m/d/Y", strtotime($lastObservation[0]->obs_date));
            }

            try {
                $patientData[] = [
                    'key'                        => $patient->id,
                    // $part->id,
                    'patient_name'               => $patient->getFullName(),
                    //$meta[$part->id]["first_name"][0] . " " .$meta[$part->id]["last_name"][0],
                    'first_name'                 => $patient->getFirstName(),
                    //$meta[$part->id]["first_name"][0],
                    'last_name'                  => $patient->getLastName(),
                    //$meta[$part->id]["last_name"][0],
                    'ccm_status'                 => ucfirst($patient->getCcmStatus()),
                    //ucfirst($meta[$part->id]["ccm_status"][0]),
                    'careplan_status'            => $careplanStatus,
                    //$careplanStatus,
                    'tooltip'                    => $tooltip,
                    //$tooltip,
                    'careplan_status_link'       => $careplanStatusLink,
                    //$careplanStatusLink,
                    'careplan_provider_approver' => $approverName,
                    //$approverName,
                    'dob'                        => Carbon::parse($patient->getBirthDate())->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["birth_date"])),
                    'phone'                      => isset($patient->phoneNumbers->number)
                        ? $patient->phoneNumbers->number
                        : $patient->getPhone(),
                    //$user_config[$part->id]["study_phone_number"],
                    'age'                        => $patient->getAge(),
                    'reg_date'                   => Carbon::parse($patient->getRegistrationDate())->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["registration_date"])) ,
                    'last_read'                  => $lastObservationDate,
                    //date("m/d/Y", strtotime($last_read)),
                    'ccm_time'                   => $patient->getCcmTime(),
                    //$ccm_time[0],
                    'ccm_seconds'                => $patient->getCcmTime(),
                    //$meta[$part->id]['cur_month_activity_time'][0]
                    'provider'                   => $bpName,
                    'site'                       => $programName,
                ];
            } catch (\Exception $e) {
                \Log::critical("{$patient->id} has no patient info");
                \Log::critical("{$e} has no patient info");
            }
        }
        return $patientData;
    }

    public function patientListing(Collection $patients = null)
    {
        $patientData           = $this->patients($patients);
        $patientJson           = json_encode($patientData);
        $auth                  = \Auth::user();
        $canApproveCarePlans   = $auth->canApproveCareplans();
        $canQAApproveCarePlans = $auth->canQAApproveCarePlans();
        $isCareCenter          = $auth->hasRole('care-center');
        $isAdmin               = $auth->isAdmin();
        $isProvider            = $auth->hasRole('provider');
        $isPracticeStaff       = $auth->hasRole(['office_admin', 'med_assistant']);

        return compact([
            'patientJson',
            'canApproveCarePlans',
            'isCareCenter',
            'isAdmin',
            'isProvider',
            'isPracticeStaff',
        ]);
    }
}

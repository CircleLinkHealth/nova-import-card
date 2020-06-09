<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Formatters;

use App\Contracts\ReportFormatter;
use App\Note;
use App\Relationships\PatientCareplanRelations;
use App\Services\CPM\CpmMiscService;
use App\Services\NoteService;
use App\Services\ReportsService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use Illuminate\Database\Eloquent\Collection;

class WebixFormatter implements ReportFormatter
{
    private $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function formatDataForNotesAndOfflineActivitiesReport($patient)
    {
        $billingProvider = $patient->getBillingProviderName();

        $notes = $patient->notes->sortByDesc('id')->map(
            function (Note $note) use ($patient, $billingProvider) {
                $result = [
                    'id'          => $note->id,
                    'logger_id'   => $note->author_id,
                    'logger_name' => $note->author->getFullName(),
                    'comment'     => empty($note->summary)
                        ? $note->body
                        : $note->summary,
                    'logged_from'      => 'note',
                    'type_name'        => $note->type,
                    'performed_at'     => presentDate($note->performed_at, false),
                    'date_for_sorting' => $note->performed_at,
                    'provider_name'    => $billingProvider,
                    'tags'             => '',
                    'status'           => $note->status,
                    'success_story'    => $note->success_story,
                ];

                if (empty($result['type_name'])) {
                    $result['type_name'] = 'NA';
                }

                if (Note::STATUS_DRAFT === $note->status) {
                    if ($note->author_id === auth()->id()) {
                        $editNoteRoute = route(
                            'patient.note.edit',
                            ['patientId' => $note->patient_id, 'noteId' => $note->id]
                        );
                        $result['tags'] .= "<div style='display: inline;'><a href='$editNoteRoute'><span class='glyphicon glyphicon-pencil' style='position: relative; top: 1px' aria-hidden=\"true\"></span> <span>Draft</span></a></div> ";
                    } else {
                        $result['tags'] .= '<div style="display: inline"><span>Draft</span></div> ';
                    }
                }

                //pangratios: add support for task types
                if ($note->call && 'task' === $note->call->type) {
                    $result['logged_from'] = 'note_task';
                }

                if ($note->notifications->count() > 0) {
                    if ($this->noteService->wasForwardedToCareTeam($note)) {
                        $result['tags'] .= '<div class="label label-warning" style="top: -2px; position: relative;"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
                    }
                }

                if ($note->call && 'reached' == $note->call->status) {
                    $result['tags'] .= '<div class="label label-info" style="top: -2px; position: relative;"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
                }

                if ($note->isTCM) {
                    $result['tags'] .= '<div class="label label-danger" style="top: -2px; position: relative;"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
                }

                $was_seen = $this->noteService->wasSeenByBillingProvider($note);

                if ($was_seen) {
                    $result['tags'] .= '<div class="label label-success" style="top: -2px; position: relative;"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></div> ';
                }

                if ($note->success_story) {
                    $result['tags'] .= '<div class="label label-warning" style="top: -2px; position: relative; background-color: #9865f2" ><span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span></div> ';
                }

                return $result;
            }
        );

        if ($notes->isEmpty()) {
            $notes = collect([]);
        }

        $appointments = $patient->appointments->map(
            function ($appointment) use ($billingProvider) {
                return [
                    'id'               => $appointment->id,
                    'logger_name'      => optional($appointment->author)->getFullName(),
                    'comment'          => $appointment->comment,
                    'logged_from'      => 'appointment',
                    'type_name'        => $appointment->type,
                    'performed_at'     => presentDate($appointment->date, false),
                    'date_for_sorting' => $appointment->date,
                    'provider_name'    => $billingProvider,
                    'tags'             => '',
                ];
            }
        );

        if ($appointments->isEmpty()) {
            $appointments = collect([]);
        }

        $activities = $patient->activities->map(
            function ($activity) use ($billingProvider) {
                return [
                    'id'               => $activity->id,
                    'logger_name'      => $activity->provider->getFullName(),
                    'comment'          => $activity->getCommentForActivity() ?? '',
                    'logged_from'      => 'manual_input',
                    'type_name'        => $activity->type,
                    'performed_at'     => presentDate($activity->performed_at, false),
                    'date_for_sorting' => $activity->performed_at,
                    'provider_name'    => $billingProvider,
                    'tags'             => '',
                ];
            }
        );

        if ($activities->isEmpty()) {
            $activities = collect([]);
        }

        $report_data = $notes->merge($appointments)
            ->merge($activities)
            ->sortByDesc('date_for_sorting')
            ->values()
            ->toJson();

        if ( ! empty($report_data)) {
            return $report_data;
        }

        return '';
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
            $formatted_notes[$count]['summary'] = $note->summary ?? $note->body;
            $formatted_notes[$count]['comment'] = $note->body;

            $formatted_notes[$count]['date'] = Carbon::parse($note->performed_at)->format('Y-m-d');

            //TAGS
            $formatted_notes[$count]['tags'] = '';

            if ($this->noteService->wasForwardedToCareTeam($note)) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-warning"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div> ';
            }

            if ($note->call && 'reached' == $note->call->status) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-info"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span></div> ';
            }

            if ($note->isTCM) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-danger"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></div> ';
            }

            $was_seen = $this->noteService->wasSeenByBillingProvider($note);

            if ($was_seen) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-success"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></div> ';
            }

            if ($note->success_story) {
                $formatted_notes[$count]['tags'] .= '<div class="label label-warning" style="top: -2px; position: relative; background-color: #9865f2" ><span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span></div>';
            }

            ++$count;
        }

        return $formatted_notes;
    }

    public function formatDataForViewPrintCareplanReport($user)
    {
        $careplanReport    = [];
        $cpmProblemService = app(\App\Services\CPM\CpmProblemService::class);

        $user->loadMissing(PatientCareplanRelations::get());

        $careplanReport[$user->id] = [
            'symptoms'    => $user->cpmSymptoms->pluck('name')->all(),
            'problem'     => $user->cpmProblems->sortBy('name')->pluck('name')->all(),
            'problems'    => $cpmProblemService->getProblemsWithInstructionsForUser($user),
            'lifestyle'   => $user->cpmLifestyles->pluck('name')->all(),
            'biometrics'  => $user->cpmBiometrics->pluck('name')->all(),
            'medications' => $user->cpmMedicationGroups->pluck('name')->all(),
        ];

        $other_problems = (new ReportsService())->getInstructionsforOtherProblems($user);

        if ( ! empty($other_problems) && isset($careplanReport[$user->id], $careplanReport[$user->id]['problems'])) {
            if ( ! is_string($careplanReport[$user->id]['problems'])) {
                $careplanReport[$user->id]['problems']['Full Conditions List'] = $other_problems;
            }
        }

        //Get Biometrics with Values
        $careplanReport[$user->id]['bio_data'] = [];

        //Ignore Smoking - Untracked Biometric
        if (false !== ($key = array_search(CpmBiometric::SMOKING, $careplanReport[$user->id]['biometrics']))) {
            unset($careplanReport[$user->id]['biometrics'][$key]);
        }

        foreach ($careplanReport[$user->id]['biometrics'] as $metric) {
            $biometric        = $user->cpmBiometrics->where('name', $metric)->first();
            $biometric_values = app(config('cpmmodelsmap.biometrics')[$biometric->type])->getUserValues($user);

            if ($biometric_values) {
                //Check to see whether the user has a starting value
                if ('' == $biometric_values['starting']) {
                    $biometric_values['starting'] = 'N/A';
                }

                //Check to see whether the user has a target value
                if ('' == $biometric_values['target']) {
                    $biometric_values['target'] = 'TBD';
                }

                //If no values are retrievable, then default to these:
            } else {
                $biometric_values['starting'] = 'N/A';
                $biometric_values['target']   = 'TBD';
            }

            //Special verb use for each biometric
            if ('Blood Pressure' == $metric) {
                if ('N/A' == $biometric_values['starting'] || 'TBD' == $biometric_values['target']) {
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

            if ('Weight' == $metric) {
                if ('N/A' == $biometric_values['starting'] || 'TBD' == $biometric_values['target']) {
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

            if ('Blood Sugar' == $metric) {
                if ('N/A' == $biometric_values['starting'] || 'TBD' == $biometric_values['target']) {
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

            $careplanReport[$user->id]['bio_data'][$metric]['target'] = $biometric_values['target'].ReportsService::biometricsUnitMapping(
                $metric
            );
            $careplanReport[$user->id]['bio_data'][$metric]['starting'] = $biometric_values['starting'].ReportsService::biometricsUnitMapping(
                $metric
            );
            $careplanReport[$user->id]['bio_data'][$metric]['verb'] = $biometric_values['verb'];
        }

        //Medications List
        $careplanReport[$user->id]['taking_meds'] = $this->sectionTakingMeds($user->ccdMedications);

        //Allergies
        $careplanReport[$user->id]['allergies'] = 'No instructions at this time';

        $allergies = $user->ccdAllergies
            ->sortBy('allergen_name')
            ->unique('allergen_name')
            ->values();

        if ($allergies->isNotEmpty()) {
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
                ++$i;
            }
        }

        $miscService = app(CpmMiscService::class);

        //Social Services
        if ($user->cpmMiscUserPivot->contains('cpmMisc.name', CpmMisc::SOCIAL_SERVICES)) {
            $careplanReport[$user->id]['social'] = $miscService->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::SOCIAL_SERVICES
            );
        } else {
            $careplanReport[$user->id]['social'] = '';
        }

        //Other
        if ($user->cpmMiscUserPivot->contains('cpmMisc.name', CpmMisc::OTHER)) {
            $careplanReport[$user->id]['other'] = $miscService->getMiscWithInstructionsForUser(
                $user,
                CpmMisc::OTHER
            );
        } else {
            $careplanReport[$user->id]['other'] = '';
        }

        $careplanReport[$user->id]['appointments'] = null;

        //Appointments
        //Upcoming
        $upcoming = $user->appointments
            ->where('date', '>', Carbon::now()->toDateString())
            ->sortBy('date')
            ->take(3);

        foreach ($upcoming as $appt) {
            $provider = $appt->provider;

            $phone     = null;
            $specialty = null;
            if ($provider) {
                $specialty = $provider->getSpecialty() ?? null;
                if ($specialty) {
                    $specialty = '('.$specialty.')';
                }

                //format super specific phone number requirements
                if ($provider->getPrimaryPhone()) {
                    $phone = 'P: '.preg_replace(
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
                'time'      => Carbon::parse($appt->time)->format('H:i A').' '.Carbon::parse($user->timezone)->format(
                    'T'
                ),
                'address' => optional($provider)->address
                    ? "A: {$provider->address}. "
                    : '',
                'phone' => $phone,
            ];

            $careplanReport[$user->id]['appointments']['upcoming'] = $formattedUpcomingAppointment;
        }

        //past
        $past = $user->appointments
            ->where('date', '<', Carbon::now()->toDateString())
            ->sortByDesc('date')
            ->take(3);

        foreach ($past as $appt) {
            $provider = $appt->provider;

            if ( ! $provider) {
                continue;
            }

            $specialty = $provider->getSpecialty() ?? null;
            if ($specialty) {
                $specialty = '('.$specialty.')';
            }

            //format super specific phone number requirements
            if ($provider->getPrimaryPhone()) {
                $phone = 'P: '.preg_replace(
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
                    ? "{$appt->type},"
                    : '',
                'time' => Carbon::parse($appt->time)->format('H:i A').' '.Carbon::parse($user->timezone)->format(
                    'T'
                ),
                'address' => $provider->address
                    ? "A: {$provider->address}. "
                    : '',
                'phone' => $phone,
            ];

            $careplanReport[$user->id]['appointments']['past'] = $formattedPastAppointment;
        }

        return $careplanReport;
    }

    public function patientListing(Collection $patients = null)
    {
        $patientData           = $this->patients($patients);
        $patientJson           = json_encode($patientData);
        $auth                  = \Auth::user();
        $canApproveCarePlans   = $auth->canApproveCareplans();
        $canQAApproveCarePlans = $auth->canQAApproveCarePlans();
        $isCareCenter          = $auth->isCareCoach();
        $isAdmin               = $auth->isAdmin();
        $isProvider            = $auth->isProvider();
        $isPracticeStaff       = $auth->hasRole(['office_admin', 'med_assistant']);

        return compact(
            [
                'patientJson',
                'canApproveCarePlans',
                'isCareCenter',
                'isAdmin',
                'isProvider',
                'isPracticeStaff',
            ]
        );
    }

    public function patients(Collection $patients = null)
    {
        $patientData = [];
        /** @var User $auth */
        $auth = \Auth::user();

        if ( ! $patients) {
            $patients = $auth->patientList();
        }

        $foundUsers    = []; // save resources, no duplicate db calls
        $foundPrograms = []; // save resources, no duplicate db calls

        $canApproveCarePlans   = $auth->canApproveCarePlans();
        $canQAApproveCarePlans = $auth->canQAApproveCarePlans();
        $isCareCenter          = $auth->isCareCoach();
        $isAdmin               = $auth->isAdmin();
        $isProvider            = $auth->isProvider();
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

            if ('provider_approved' == $careplanStatus) {
                $approver = $patient->carePlan->providerApproverUser;
                if ($approver) {
                    $approverName = $approver->getFullName();
                }

                $carePlanProviderDate = $patient->carePlan->provider_date;
                $careplanStatus       = 'Approved';
                $careplanStatusLink   = '<span data-toggle="" title="'.$approverName.' '.$carePlanProviderDate.'">Approved</span>';
                $tooltip              = $approverName.' '.$carePlanProviderDate;
            } else {
                if ('qa_approved' == $careplanStatus) {
                    $careplanStatus     = 'Approve Now';
                    $tooltip            = $careplanStatus;
                    $careplanStatusLink = 'Approve Now';
                    if ($canApproveCarePlans) {
                        $careplanStatusLink = '<a style="text-decoration:underline;" href="'.route(
                            'patient.careplan.print',
                            ['patientId' => $patient->id]
                        ).'"><strong>Approve Now</strong></a>';
                    }
                } else {
                    if ('draft' == $careplanStatus) {
                        $careplanStatus     = 'CLH Approve';
                        $tooltip            = $careplanStatus;
                        $careplanStatusLink = 'CLH Approve';
                        if ($canQAApproveCarePlans) {
                            $careplanStatusLink = '<a style="text-decoration:underline;" href="'.route(
                                'patient.demographics.show',
                                [$patient->id]
                            ).'"><strong>CLH Approve</strong></a>';
                        }
                    }
                }
            }

            // get billing provider name
            $bpName = '';
            $bpID   = $patient->billingProviderID;
            if ( ! isset($foundPrograms[$patient->program_id])) {
                $program                             = $patient->primaryPractice;
                $foundPrograms[$patient->program_id] = $program;
            } else {
                $program = $foundPrograms[$patient->program_id];
            }

            if ( ! $program) {
                \Log::critical("Patient with id:{$patient->id} does not have Practice attached.");
            }

            $programName = optional($program)->display_name ?? '';

            $bpCareTeamMember = $patient->careTeamMembers->first();

            if ($bpCareTeamMember) {
                $bpUser = $bpCareTeamMember->user;

                if ( ! $bpUser) {
                    continue;
                }

                $bpName            = $bpUser->getFullName();
                $foundUsers[$bpID] = $bpUser;
            }

            // get date of last observation
            $lastObservationDate = 'No Readings';
            $lastObservation     = $patient->observations;
            if ($lastObservation->count() > 0) {
                $lastObservationDate = date('m/d/Y', strtotime($lastObservation[0]->obs_date));
            }

            $locationName = $patient->getPreferredLocationName();

            try {
                $patientData[] = [
                    'key' => $patient->id,
                    // $part->id,
                    'patient_name' => $patient->getFullName(),
                    //$meta[$part->id]["first_name"][0] . " " .$meta[$part->id]["last_name"][0],
                    'first_name' => $patient->getFirstName(),
                    //$meta[$part->id]["first_name"][0],
                    'last_name' => $patient->getLastName(),
                    //$meta[$part->id]["last_name"][0],
                    'ccm_status' => ucfirst($patient->getCcmStatus()),
                    //ucfirst($meta[$part->id]["ccm_status"][0]),
                    'careplan_status'  => $careplanStatus,
                    'withdrawn_reason' => $patient->getWithdrawnReason(),
                    //$careplanStatus,
                    'tooltip' => $tooltip,
                    //$tooltip,
                    'careplan_status_link' => $careplanStatusLink,
                    //$careplanStatusLink,
                    'careplan_provider_approver' => $approverName,
                    //$approverName,
                    'dob' => Carbon::parse($patient->getBirthDate())->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["birth_date"])),
                    'mrn'   => $patient->getMRN(),
                    'phone' => isset($patient->phoneNumbers->number)
                        ? $patient->phoneNumbers->number
                        : $patient->getPhone(),
                    //$user_config[$part->id]["study_phone_number"],
                    'age'      => $patient->getAge(),
                    'reg_date' => Carbon::parse($patient->getRegistrationDate())->format('m/d/Y'),
                    //date("m/d/Y", strtotime($user_config[$part->id]["registration_date"])) ,
                    'last_read' => $lastObservationDate,
                    //date("m/d/Y", strtotime($last_read)),
                    'ccm_time' => $patient->getCcmTime(),
                    //$ccm_time[0],
                    'ccm_seconds' => $patient->getCcmTime(),
                    //$meta[$part->id]['cur_month_activity_time'][0]
                    'provider' => $bpName,
                    'site'     => $programName,
                    'location' => $locationName,
                ];
            } catch (\Exception $e) {
                \Log::critical("{$patient->id} has no patient info");
                \Log::critical("{$e} has no patient info");
            }
        }

        return $patientData;
    }

    private function sectionTakingMeds(iterable $medications)
    {
        foreach ($medications as $med) {
            $medText = empty($med->name)
                ? ''
                : $med->name;

            if ( ! empty($med->sig)) {
                $medText .= '<br /><span style="font-style:italic;">- '.$med->sig.'</span>';
            }
            $result[] = $medText;
        }

        return $result ?? 'No instructions at this time';
    }
}

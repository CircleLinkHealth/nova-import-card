<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API\Admin;

use App\Call;
use App\Filters\CallFilters;
use App\Filters\PatientFilters;
use App\Http\Controllers\API\ApiController;
use App\Http\Resources\Call as CallResource;
use App\Http\Resources\User as UserResource;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Role;
use App\Services\Calls\ManagementService;
use App\Services\CallService;
use App\Services\NoteService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class CallsController extends ApiController
{
    private $callService;
    private $noteService;
    private $service;

    public function __construct(ManagementService $service, NoteService $noteService, CallService $callService)
    {
        $this->service     = $service;
        $this->noteService = $noteService;
        $this->callService = $callService;
    }

    /**
     * @SWG\GET(
     *     path="/admin/calls",
     *     tags={"calls"},
     *     summary="Get Calls Info",
     *     description="Display a listing of calls",
     *     @SWG\Header(header="X-Requested-With", type="String", default="XMLHttpRequest"),
     *     @SWG\Response(
     *         response="default",
     *         description="A listing of calls"
     *     )
     *   )
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, CallFilters $filters)
    {
        $rows  = $request->input('rows');
        $calls = Call::whereHas('inboundUser', function ($q) {
            $q->whereHas('primaryPractice', function ($q) {
                $q->where('active', 1);
            })->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', Patient::ENROLLED);
            });
        })
            ->with('schedulerUser.roles')
            ->filter($filters)
            ->paginate($rows ?? 15);

        return CallResource::collection($calls);
    }

    public function patientsWithoutInboundCalls(PatientFilters $filters, $practiceId = null)
    {
        $patients = $this->service->getPatientsWithoutAnyInboundCalls($practiceId, Carbon::now())
            ->filter($filters)->get();

        if ($filters->isAutocomplete()) {
            return $patients->map(function ($patient) {
                return $patient->autocomplete();
            });
        }

        return UserResource::collection($patients);
    }

    public function patientsWithoutScheduledActivities(PatientFilters $filters, $practiceId = null)
    {
        $user = auth()->user();

        if ( ! $user->isAdmin()) {
            //if we have $practiceId, make sure that user has access to it
            if ($practiceId) {
                if ( ! $user->hasRoleForSite('software-only', $practiceId)) {
                    abort(403);
                }
            } else {
                //if no $practiceId, get all practice ids where user is software-only / practice admin
                $roleIds    = Role::getIdsFromNames(['software-only']);
                $practiceId = $user->practices(true, false, $roleIds)->pluck('id')->toArray();
            }
        }

        $patients = $this->service->getPatientsWithoutScheduledActivities($practiceId, Carbon::now())
            ->filter($filters)->get();

        if ($filters->isAutocomplete()) {
            return $patients->map(function ($patient) {
                return $patient->autocomplete();
            });
        }

        return UserResource::collection($patients);
    }

    public function patientsWithoutScheduledCalls(PatientFilters $filters, $practiceId = null)
    {
        $user = auth()->user();

        if ( ! $user->isAdmin()) {
            //if we have $practiceId, make sure that user has access to it
            if ($practiceId) {
                if ( ! $user->hasRoleForSite('software-only', $practiceId)) {
                    abort(403);
                }
            } else {
                //if no $practiceId, get all practice ids where user is software-only / practice admin
                $roleIds    = Role::getIdsFromNames(['software-only']);
                $practiceId = $user->practices(true, false, $roleIds)->pluck('id');
            }
        }

        $patients = $this->service->getPatientsWithoutScheduledCalls($practiceId, Carbon::now())
            ->filter($filters)->get();

        if ($filters->isAutocomplete()) {
            return $patients->map(function ($patient) {
                return $patient->autocomplete();
            });
        }

        return UserResource::collection($patients);
    }

    /**
     * Remove the calls with IDs from storage.
     *
     * @param string $ids
     *
     * @return \Illuminate\Http\Response
     */
    public function remove($ids)
    {
        if (str_contains($ids, ',')) {
            $ids = explode(',', $ids);
        }

        if ( ! is_array($ids)) {
            $ids = [$ids];
        }

        $this->callService->repo()->model()->whereIn('id', $ids)
            ->delete();

        return response()->json($ids);
    }

    public function show($id)
    {
        return $this->json($this->callService->repo()->call($id));
    }

    public function toBeDeprecatedIndex()
    {
        $date = Carbon::now()->startOfMonth();

        $calls = Call::whereHas('inboundUser')
            ->with('outboundUser')
            ->with('note')
            ->select(
                [
                    \DB::raw('coalesce(nurse.display_name, "unassigned") as nurse_name'),
                    'calls.id AS call_id',
                    'calls.status',
                    'calls.outbound_cpm_id',
                    'calls.inbound_cpm_id',
                    'calls.scheduled_date',
                    'calls.window_start',
                    'calls.window_end',
                    'calls.window_end AS window_end_value',
                    'calls.attempt_note',
                    'nurse_info.user_id as nurse_id',
                    'nurse_info.status as nurse_status',
                    'notes.type AS note_type',
                    'notes.body AS note_body',
                    'notes.performed_at AS note_datetime',
                    'calls.note_id',
                    'calls.scheduler AS scheduler',
                    'scheduler_user.display_name AS scheduler_user_name',
                    'patient_monthly_summaries.ccm_time',
                    'patient_info.last_successful_contact_time',
                    \DB::raw('DATE_FORMAT(patient_info.last_contact_time, "%Y-%m-%d") as last_contact_time'),
                    \DB::raw('coalesce(patient_info.no_call_attempts_since_last_success, "n/a") as no_call_attempts_since_last_success'),
                    'patient_info.ccm_status',
                    'patient_info.birth_date',
                    'patient_info.general_comment',
                    'patient_monthly_summaries.is_ccm_complex',
                    'patient_monthly_summaries.no_of_calls',
                    'patient_monthly_summaries.no_of_successful_calls',
                    'patient.timezone AS patient_timezone',
                    \DB::raw('CONCAT_WS(", ", patient.last_name, patient.first_name) AS patient_name'),
                    'program.display_name AS program_name',
                    'billing_provider.display_name AS billing_provider',
                ]
                     )
//            ->where('nurse_info.status', '=', 'active')
//            ->orWhere('nurse_name')
            ->where('calls.status', '=', 'scheduled')
            ->leftJoin('notes', 'calls.note_id', '=', 'notes.id')
            ->leftJoin('nurse_info', 'calls.outbound_cpm_id', '=', 'nurse_info.user_id')
            ->leftJoin('users AS nurse', 'calls.outbound_cpm_id', '=', 'nurse.id')
            ->leftJoin('users AS patient', 'calls.inbound_cpm_id', '=', 'patient.id')
            ->leftJoin('users AS scheduler_user', 'calls.scheduler', '=', 'scheduler_user.id')
            ->leftJoin('patient_info', 'calls.inbound_cpm_id', '=', 'patient_info.user_id')
            ->leftJoin('patient_monthly_summaries', function ($join) use (
                         $date
                     ) {
                $join->on('patient_monthly_summaries.patient_id', '=', 'patient_info.user_id');
                $join->where('patient_monthly_summaries.month_year', '=', $date->format('Y-m-d'));
            })
            ->leftJoin('practices AS program', 'patient.program_id', '=', 'program.id')
            ->leftJoin('patient_care_team_members', function ($join) {
                $join->on('patient.id', '=', 'patient_care_team_members.user_id');
                $join->where('patient_care_team_members.type', '=', 'billing_provider');
            })
            ->leftJoin(
                'users AS billing_provider',
                'patient_care_team_members.member_user_id',
                '=',
                'billing_provider.id'
                     )
            ->groupBy('call_id')
            ->get();

        return Datatables::of($calls)
            ->editColumn('call_id', function ($call) {
                return '<input type="checkbox" name="calls[]" value="'.$call->call_id.'">';
            })
            ->addColumn('general_comment_html', function ($call) {
                $generalComment = 'Add Text';
                if ( ! empty($call->general_comment)) {
                    $generalComment = $call->general_comment;
                }

                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="general_comment" column-value="'.$generalComment.'">'.$generalComment.'</span>';
            })
            ->addColumn('attempt_note_html', function ($call) {
                $attemptNote = 'Add Text';
                if ( ! empty($call->attempt_note)) {
                    $attemptNote = $call->attempt_note;
                }

                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="attempt_note" column-value="'.$attemptNote.'">'.$attemptNote.'</span>';
            })
            ->addColumn('ccm_complex', function ($call) {
                $isCcmComplex = null;

                if ($call->inboundUser) {
                    $isCcmComplex = $call->inboundUser->patientInfo
                                     ? $call->inboundUser->isCCMComplex()
                                     : null;
                }

                if ($isCcmComplex) {
                    return '<span id="complex_tag" hidden style="background-color: #ec683e;" class="label label-warning"> Complex CCM</span>';
                }

                return '';
            })
            ->editColumn('scheduled_date', function ($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="scheduled_date" column-value="'.$call->scheduled_date.'">'.$call->scheduled_date.'</span>';
            })
            ->editColumn('window_start', function ($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="window_start" column-value="'.$call->window_start.'">'.$call->window_start.'</span>';
            })
            ->editColumn('window_end', function ($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="window_end" column-value="'.$call->window_end.'">'.$call->window_end.'</span>';
            })
            ->editColumn('status', function ($call) {
                if ('reached' == $call->status) {
                    return '<span class="btn btn-success btn-xs"><i class="glyphicon glyphicon-ok"></i> Reached</span>';
                }
                if ('scheduled' == $call->status) {
                    return '<span class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-list"></i> Scheduled</span>';
                }
            })
            ->editColumn('patient_name', function ($call) {
                return '<a href="'.\route(
                    'patient.demographics.show',
                    ['patientId' => $call->inboundUser->id]
                                 ).'" target="_blank">'.$call->patient_name.'</span>';
            })
            ->editColumn('nurse_name', function ($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="outbound_cpm_id" column-value="'.$call->outbound_cpm_id.'">'.$call->nurse_name.'</span>';
            })
            ->editColumn('ccm_time', function ($call) {
                if ($call->inboundUser) {
                    $seconds = $call->inboundUser->getCcmTime();
                    $H = floor($seconds / 3600);
                    $i = ($seconds / 60) % 60;
                    $s = $seconds % 60;
                    $monthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);

                    return substr($monthlyTime, 1);
                }

                return 'n/a';
            })
            ->editColumn('scheduler', function ($call) {
                if ( ! empty($call->scheduler_user_name)) {
                    return $call->scheduler_user_name;
                }

                return $call->scheduler;
            })
            ->editColumn('no_call_attempts_since_last_success', function ($call) {
                if ('n/a' == $call->no_call_attempts_since_last_success) {
                    return 'n/a';
                }
                if ($call->no_call_attempts_since_last_success > 0) {
                    return $call->no_call_attempts_since_last_success.'x Attempts';
                }

                return 'Success';
            })
            ->addColumn('patient_call_windows', function ($call) {
                $days = [
                    1 => 'M',
                    2 => 'Tu',
                    3 => 'W',
                    4 => 'Th',
                    5 => 'F',
                    6 => 'Sa',
                    7 => 'Su',
                ];
                $windowText = '';
                if ($call->inboundUser && $call->inboundUser->patientInfo) {
                    $windows = $call->inboundUser->patientInfo->contactWindows()->get();
                    if ($windows) {
                        $windowText .= '<ul>';
                        foreach ($days as $key => $val) {
                            foreach ($windows as $window) {
                                if ($window->day_of_week == $key) {
                                    $windowText .= '<li>';
                                    $windowText .= $days[$window->day_of_week].': '.$window->window_time_start.' - '.$window->window_time_end;
                                    $windowText .= '</li>';
                                }
                            }
                        }
                        $windowText .= '</ul>';
                    }
                }

                return $windowText;
            })
            ->addColumn('patient_call_window_days_short', function ($call) {
                $days = [
                    1 => 'M',
                    2 => 'Tu',
                    3 => 'W',
                    4 => 'Th',
                    5 => 'F',
                    6 => 'Sa',
                    7 => 'Su',
                ];
                if ($call->inboundUser && $call->inboundUser->patientInfo) {
                    $windowText = '';
                    $windows = $call->inboundUser->patientInfo->contactWindows()->get();
                    if ($windows) {
                        foreach ($days as $key => $val) {
                            foreach ($windows as $window) {
                                if ($window->day_of_week == $key) {
                                    $windowText .= $days[$window->day_of_week].',';
                                }
                            }
                        }
                    }

                    return rtrim($windowText, ',');
                }

                return 'n/a';
            })
            ->addColumn('patient_timezone', function ($call) {
                $dateTime = new DateTime();
                $dateTime->setTimeZone(new DateTimeZone($call->patient_timezone));

                return '<span style="font-weight:bold;color:green;">'.$dateTime->format('T').'</a>';
            })
            ->addColumn('notes_link', function ($call) {
                if ( ! $call) {
                    return '';
                }

                return '<a target="_blank" href="'.\route(
                    'patient.note.index',
                    ['patientId' => $call->inboundUser->id]
                                 ).'">Notes</a>';
            })
            ->addColumn('notes_html', function ($call) {
                $notesHtml = '';
                if ($call->inboundUser) {
                    $notes = $call->inboundUser->notes()->with(['call', 'notifications'])->orderBy(
                        'performed_at',
                        'desc'
                                 )->limit(3)->get();
                    if ($notes->count() > 0) {
                        $notesHtml .= '<ul>';
                        foreach ($notes as $note) {
                            $notesHtml .= '<li style="width:800px;margin:5px 0px;white-space:normal;">';
                            $notesHtml .= 'Note '.$note->performed_at.': ';

                            //Call Info
                            if (count($note->call) > 0) {
                                if ($note->call->is_cpm_inbound) {
                                    $notesHtml .= '<div class="label label-info" style="margin:5px;">Inbound Call</div>';
                                } else {
                                    $notesHtml .= '<div class="label label-info" style="margin:5px;">Outbound Call</div>';
                                }

                                if ('reached' == $note->call->status) {
                                    $notesHtml .= '<div class="label label-info" style="margin:5px;">Successful Clinical Call</div>';
                                }

                                if ($this->noteService->getForwards($note)->count() > 0) {
                                    $mailText = 'Forwarded: ';
                                    foreach ($this->noteService->getForwards($note) as $name => $forwardedAt) {
                                        if ($name) {
                                            $mailText .= $name.', ';
                                        }
                                    }
                                    $notesHtml .= '<div class="label label-info" style="margin:5px;" data-toggle="tooltip" title="'.rtrim(
                                        $mailText,
                                        ','
                                                     ).'">Forwarded</div>';
                                }
                            }
                            if ($note->isTCM) {
                                $notesHtml .= '<div class="label label-danger">Patient Recently in Hospital/ER</div>';
                            }
                            $notesHtml .= '<span style="font-weight:bold;">'.$note->type.'</span> ';
                            $notesHtml .= $note->body;
                            $notesHtml .= '</li>';
                        }
                        $notesHtml .= '</ul>';
                    }
                }

                return $notesHtml;
            })
            ->addColumn('background_color', function ($call) {
                $curTime = Carbon::now();
                $curDate = $curTime->toDateString();
                $curTime = $curTime->toTimeString();
                if ($call->scheduled_date == $curDate && $call->window_end < $curTime) {
                    return 'rgba(255, 0, 0, 0.4)';
                }

                return '';
            })
            ->addColumn('blank', function ($call) {
                return '';
            })
            ->make(true);
    }
}

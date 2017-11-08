<?php

namespace App\Http\Controllers\API\Admin;

use App\Call;
use App\Http\Controllers\Controller;
use App\Http\Resources\Call as CallResource;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;


class CallsController extends Controller
{
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
                             'patient_info.cur_month_activity_time',
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
                         $join->on('patient_monthly_summaries.patient_info_id', '=', 'patient_info.id');
                         $join->where('patient_monthly_summaries.month_year', '=', $date->format('Y-m-d'));
                     })
                     ->leftJoin('practices AS program', 'patient.program_id', '=', 'program.id')
                     ->leftJoin('patient_care_team_members', function ($join) {
                         $join->on('patient.id', '=', 'patient_care_team_members.user_id');
                         $join->where('patient_care_team_members.type', '=', "billing_provider");
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
                             return '<input type="checkbox" name="calls[]" value="' . $call->call_id . '">';
                         })
                         ->addColumn('general_comment_html', function ($call) {
                             $generalComment = 'Add Text';
                             if ( ! empty($call->general_comment)) {
                                 $generalComment = $call->general_comment;
                             }

                             return '<a href="#"><span class="cpm-editable-icon" call-id="' . $call->call_id . '" column-name="general_comment" column-value="' . $generalComment . '">' . $generalComment . '</span>';
                         })
                         ->addColumn('attempt_note_html', function ($call) {
                             $attemptNote = 'Add Text';
                             if ( ! empty($call->attempt_note)) {
                                 $attemptNote = $call->attempt_note;
                             }

                             return '<a href="#"><span class="cpm-editable-icon" call-id="' . $call->call_id . '" column-name="attempt_note" column-value="' . $attemptNote . '">' . $attemptNote . '</span>';
                         })
                         ->addColumn('ccm_complex', function ($call) {
                             $isCcmComplex = null;

                             if ($call->inboundUser) {
                                 $isCcmComplex = $call->inboundUser->patientInfo
                                     ? $call->inboundUser->patientInfo->isCCMComplex()
                                     : null;
                             }

                             if ($isCcmComplex) {
                                 return "<span id=\"complex_tag\" hidden style=\"background-color: #ec683e;\" class=\"label label-warning\"> Complex CCM</span>";
                             } else {
                                 return '';
                             }
                         })
                         ->editColumn('scheduled_date', function ($call) {
                             return '<a href="#"><span class="cpm-editable-icon" call-id="' . $call->call_id . '" column-name="scheduled_date" column-value="' . $call->scheduled_date . '">' . $call->scheduled_date . '</span>';
                         })
                         ->editColumn('window_start', function ($call) {
                             return '<a href="#"><span class="cpm-editable-icon" call-id="' . $call->call_id . '" column-name="window_start" column-value="' . $call->window_start . '">' . $call->window_start . '</span>';
                         })
                         ->editColumn('window_end', function ($call) {
                             return '<a href="#"><span class="cpm-editable-icon" call-id="' . $call->call_id . '" column-name="window_end" column-value="' . $call->window_end . '">' . $call->window_end . '</span>';
                         })
                         ->editColumn('status', function ($call) {
                             if ($call->status == 'reached') {
                                 return '<span class="btn btn-success btn-xs"><i class="glyphicon glyphicon-ok"></i> Reached</span>';
                             } elseif ($call->status == 'scheduled') {
                                 return '<span class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-list"></i> Scheduled</span>';
                             }
                         })
                         ->editColumn('patient_name', function ($call) {
                             return '<a href="' . \URL::route(
                                     'patient.demographics.show',
                                     ['patientId' => $call->inboundUser->id]
                                 ) . '" target="_blank">' . $call->patient_name . '</span>';
                         })
                         ->editColumn('nurse_name', function ($call) {
                             return '<a href="#"><span class="cpm-editable-icon" call-id="' . $call->call_id . '" column-name="outbound_cpm_id" column-value="' . $call->outbound_cpm_id . '">' . $call->nurse_name . '</span>';
                         })
                         ->editColumn('cur_month_activity_time', function ($call) {
                             if ($call->inboundUser && $call->inboundUser->patientInfo) {
                                 return substr($call->inboundUser->patientInfo->currentMonthCCMTime, 1);
                             } else {
                                 return 'n/a';
                             }
                         })
                         ->editColumn('scheduler', function ($call) {
                             if ( ! empty($call->scheduler_user_name)) {
                                 return $call->scheduler_user_name;
                             } else {
                                 return $call->scheduler;
                             }
                         })
                         ->editColumn('no_call_attempts_since_last_success', function ($call) {
                             if ($call->no_call_attempts_since_last_success == 'n/a') {
                                 return 'n/a';
                             } else {
                                 if ($call->no_call_attempts_since_last_success > 0) {
                                     return $call->no_call_attempts_since_last_success . 'x Attempts';
                                 } else {
                                     return 'Success';
                                 }
                             }
                         })
                         ->addColumn('patient_call_windows', function ($call) {
                             $days       = [
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
                                                 $windowText .= $days[$window->day_of_week] . ': ' . $window->window_time_start . ' - ' . $window->window_time_end;
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
                                 $windows    = $call->inboundUser->patientInfo->contactWindows()->get();
                                 if ($windows) {
                                     foreach ($days as $key => $val) {
                                         foreach ($windows as $window) {
                                             if ($window->day_of_week == $key) {
                                                 $windowText .= $days[$window->day_of_week] . ',';
                                             }
                                         }
                                     }
                                 }

                                 return rtrim($windowText, ',');
                             } else {
                                 return 'n/a';
                             }
                         })
                         ->addColumn('patient_timezone', function ($call) {

                             $dateTime = new DateTime();
                             $dateTime->setTimeZone(new DateTimeZone($call->patient_timezone));

                             return '<span style="font-weight:bold;color:green;">' . $dateTime->format('T') . '</a>';
                         })
                         ->addColumn('notes_link', function ($call) {

                             if ( ! $call) {
                                 return '';
                             }

                             return '<a target="_blank" href="' . \URL::route(
                                     'patient.note.index',
                                     ['patientId' => $call->inboundUser->id]
                                 ) . '">Notes</a>';
                         })
                         ->addColumn('notes_html', function ($call) {
                             $notesHtml = '';
                             if ($call->inboundUser) {
                                 $notes = $call->inboundUser->notes()->with('call')->with('mail')->orderBy(
                                     'performed_at',
                                     'desc'
                                 )->limit(3)->get();
                                 if ($notes->count() > 0) {
                                     $notesHtml .= '<ul>';
                                     foreach ($notes as $note) {
                                         $notesHtml .= '<li style="width:800px;margin:5px 0px;white-space:normal;">';
                                         $notesHtml .= 'Note ' . $note->performed_at . ': ';

                                         //Call Info
                                         if (count($note->call) > 0) {
                                             if ($note->call->is_cpm_inbound) {
                                                 $notesHtml .= '<div class="label label-info" style="margin:5px;">Inbound Call</div>';
                                             } else {
                                                 $notesHtml .= '<div class="label label-info" style="margin:5px;">Outbound Call</div>';
                                             }

                                             if ($note->call->status == 'reached') {
                                                 $notesHtml .= '<div class="label label-info" style="margin:5px;">Successful Clinical Call</div>';
                                             }

                                             if ($note->mail->count() > 0) {
                                                 $mailText = 'Forwarded: ';
                                                 foreach ($note->mail as $mail) {
                                                     if ($mail->receiverUser) {
                                                         $mailText .= $mail->receiverUser->display_name . ', ';
                                                     }
                                                 }
                                                 $notesHtml .= '<div class="label label-info" style="margin:5px;" data-toggle="tooltip" title="' . rtrim(
                                                         $mailText,
                                                         ','
                                                     ) . '">Forwarded</div>';
                                             }
                                         }
                                         if ($note->isTCM) {
                                             $notesHtml .= '<div class="label label-danger">Patient Recently in Hospital/ER</div>';
                                         }
                                         $notesHtml .= '<span style="font-weight:bold;">' . $note->type . '</span> ';
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
                             } else {
                                 return '';
                             }
                         })
                         ->addColumn('blank', function ($call) {
                             return '';
                         })
                         ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $calls = Call::where('status', '=', 'scheduled')
                     ->whereHas('inboundUser')
                     ->with([
                         'inboundUser.billingProvider.user'         => function ($q) {
                             $q->select(['id', 'first_name', 'last_name', 'suffix', 'display_name']);
                         },
                         'inboundUser.notes'                        => function ($q) {
                             $q->latest();
                         },
                         'inboundUser.patientInfo.contactWindows',
                         'inboundUser.patientInfo.monthlySummaries' => function ($q) {
                             $q->where('month_year', '=', Carbon::now()->startOfMonth()->format('Y-m-d'));
                         },
                         'inboundUser.primaryPractice'              => function ($q) {
                             $q->select(['id', 'display_name']);
                         },
                         'outboundUser.nurseInfo',
                         'note',
                     ])
                     ->paginate(30);

        return CallResource::collection($calls);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

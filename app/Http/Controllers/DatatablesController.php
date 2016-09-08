<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use Yajra\Datatables\Datatables;
use App\Call;
use Collection;
use Carbon\Carbon;

class DatatablesController extends Controller
{
    /**
     * Displays datatables front end view
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('datatables.index');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData()
    {
        //return Datatables::of(User::query())->make(true);
        //$users = DB::table('calls')->select(['id', 'call_date', 'window_start', 'window_end']);

        $users = User::select(['ID', 'display_name', 'user_email', 'created_at', 'updated_at']);

        return Datatables::of($users)->make();
    }

    public function anyCallsManagement()
    {
        $date = Carbon::now()->startOfMonth();

        $calls = Call::with('inboundUser')
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
                    'calls.attempt_note',
                    'notes.type AS note_type',
                    'notes.body AS note_body',
                    'notes.performed_at AS note_datetime',
                    'calls.note_id',
                    'patient_info.cur_month_activity_time',
                    'patient_info.last_successful_contact_time',
                    \DB::raw('DATE_FORMAT(patient_info.last_contact_time, "%Y-%m-%d") as last_contact_time'),
                    'patient_info.no_call_attempts_since_last_success',
                    'patient_info.ccm_status',
                    'patient_info.birth_date',
                    'patient_info.general_comment',
                    'patient_monthly_summaries.no_of_calls',
                    'patient_monthly_summaries.no_of_successful_calls',
                    \DB::raw('CONCAT_WS(" ", patient.last_name, patient.first_name) AS patient_name'),
                    'program.display_name AS program_name',
                    'billing_provider.display_name AS billing_provider'
                ])
            ->where('calls.status', '=', 'scheduled')
            ->leftJoin('notes', 'calls.note_id','=','notes.id')
            ->leftJoin('users AS nurse', 'calls.outbound_cpm_id','=','nurse.ID')
            ->leftJoin('users AS patient', 'calls.inbound_cpm_id','=','patient.ID')
            ->leftJoin('patient_info', 'calls.inbound_cpm_id','=','patient_info.user_id')
            ->leftJoin('patient_monthly_summaries', function($join) use ($date)
            {
                $join->on('patient_monthly_summaries.patient_info_id', '=', 'patient_info.id');
                $join->where('patient_monthly_summaries.month_year', '=', $date->format('Y-m-d'));
            })
            ->leftJoin('wp_blogs AS program', 'patient.program_id','=','program.blog_id')
            ->leftJoin('patient_care_team_members', function($join)
            {
                $join->on('patient.ID', '=', 'patient_care_team_members.user_id');
                $join->where('patient_care_team_members.type', '=', "billing_provider");
            })
            ->leftJoin('users AS billing_provider', 'patient_care_team_members.member_user_id','=','billing_provider.ID')
            ->groupBy('call_id')
            ->get();


        return Datatables::of($calls)
            ->editColumn('call_id', function($call) {
                return '<input type="checkbox" name="calls[]" value="'.$call->call_id.'">';
            })
            ->addColumn('attempt_note_html', function($call) {
                $attemptNote = 'no note';
                if(!empty($call->attempt_note)) {
                    $attemptNote = $call->attempt_note;
                }
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="attempt_note" column-value="'.$attemptNote.'">'.$attemptNote.'</span>';
            })
            ->editColumn('scheduled_date', function($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="scheduled_date" column-value="'.$call->scheduled_date.'">'.$call->scheduled_date.'</span>';
            })
            ->editColumn('window_start', function($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="window_start" column-value="'.$call->window_start.'">'.$call->window_start.'</span>';
            })
            ->editColumn('window_end', function($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="window_end" column-value="'.$call->window_end.'">'.$call->window_end.'</span>';
            })
            ->editColumn('status', function($call) {
                if($call->status == 'reached') {
                    return '<span class="btn btn-success btn-xs"><i class="glyphicon glyphicon-ok"></i> Reached</span>';
                } elseif($call->status == 'scheduled') {
                    return '<span class="btn btn-warning btn-xs"><i class="glyphicon glyphicon-list"></i> Scheduled</span>';
                }
            })
            ->editColumn('patient_name', function($call) {
                return '<a href="'.\URL::route('patient.demographics.show', array('patientId' => $call->inboundUser->ID)).'" target="_blank">'.$call->patient_name.'</span>';
            })
            ->editColumn('nurse_name', function($call) {
                return '<a href="#"><span class="cpm-editable-icon" call-id="'.$call->call_id.'" column-name="outbound_cpm_id" column-value="'.$call->outbound_cpm_id.'">'.$call->nurse_name.'</span>';
            })
            ->editColumn('cur_month_activity_time', function($call) {
                if($call->inboundUser && $call->inboundUser->patientInfo) {
                    return substr($call->inboundUser->patientInfo->currentMonthCCMTime, 1);
                } else {
                    return 'n/a';
                }
            })
            ->editColumn('no_call_attempts_since_last_success', function($call) {
                if($call->no_call_attempts_since_last_success > 0) {
                    return $call->no_call_attempts_since_last_success.'x Attempts';
                } else {
                    return 'Success';
                }
            })
            ->addColumn('patient_call_windows', function($call) {
                $days = array(
                    1 => 'M',
                    2 => 'Tu',
                    3 => 'W',
                    4 => 'Th',
                    5 => 'F',
                    6 => 'Sa',
                    7 => 'Su'
                );
                if($call->inboundUser && $call->inboundUser->patientInfo) {
                    $windowText = '';
                    $windows = $call->inboundUser->patientInfo->patientContactWindows()->get();
                    if($windows) {
                        $windowText .= '<ul>';
                        foreach($days as $key => $val) {
                            foreach ($windows as $window) {
                                if($window->day_of_week == $key) {
                                    $windowText .= '<li>';
                                    $windowText .= $days[$window->day_of_week] . ': ' . $window->window_time_start . ' - ' .$window->window_time_end;
                                    $windowText .= '</li>';
                                }
                            }
                        }
                        $windowText .= '</ul>';
                    }
                }
                return $windowText;
            })
            ->addColumn('patient_call_window_days_short', function($call) {
                $days = array(
                    1 => 'M',
                    2 => 'Tu',
                    3 => 'W',
                    4 => 'Th',
                    5 => 'F',
                    6 => 'Sa',
                    7 => 'Su'
                );
                if($call->inboundUser && $call->inboundUser->patientInfo) {
                    $windowText = '';
                    $windows = $call->inboundUser->patientInfo->patientContactWindows()->get();
                    if($windows) {
                        foreach($days as $key => $val) {
                            foreach ($windows as $window) {
                                if($window->day_of_week == $key) {
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
            ->addColumn('notes_link', function($call) {
                return '<a target="_blank" href="'.\URL::route('patient.note.index', array('patientId' => $call->inboundUser->ID)).'">Notes</a>';
            })
            ->addColumn('notes_html', function($call) {
                $notesHtml = '';
                if($call->inboundUser) {
                    $notes = $call->inboundUser->notes()->with('call')->orderBy('created_at','desc')->limit(3)->get();
                    if($notes->count() > 0) {
                        $notesHtml .= '<ul>';
                        foreach($notes as $note) {
                            $notesHtml .= '<li style="margin:5px 0px;">';
                            $notesHtml .= 'Note '.$note->created_at.': ';

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
            ->addColumn('blank', function($call) {
                return '';
            })
            ->make(true);
    }

    /*
     * This is an example of returning a collection - works great except for performance with larger result sets
     */

    /*
    public function anyDataCallsCollection()
    {
        $calls = Call::with('note')->has('note')->select(['calls.id', 'calls.call_date', 'calls.window_start', 'calls.window_end', 'notes.type', 'notes.body', 'calls.note_id'])->leftJoin('notes', 'calls.note_id','=','notes.id')->get();
        //$calls = Call::with('note')->select(['calls.*'])->get();

        $calls = Call::with('note')->get();
        $data  = [];
        foreach ($calls as $call) {
            $obj = new \stdClass;
            // call info
            $obj->id = $call->id;
            $obj->call_date = $call->call_date;
            $obj->window_start = $call->window_start;
            $obj->window_end = $call->window_end;
            $obj->type = 'n/a';
            $obj->body = 'n/a';
            if($call->note) {
                $obj->type = $call->note->type;
                $obj->body = $call->note->body;
            }
            // patient info
            $obj->patient = 'n/a';
            $obj->last_successful_contact_time = 'n/a';
            $obj->current_month_ccm_time = 'n/a';
            $obj->billing_provider = 'n/a';
            if($call->inboundUser) {
                $obj->patient = $call->inboundUser->display_name;
$call->inboundUser->patientInfo->last_successful_contact_time;
                $obj->last_successful_contact_time = $call->inboundUser->patientInfo->last_successful_contact_time;
                $obj->current_month_ccm_time = $call->inboundUser->patientInfo->currentMonthCCMTime;
                if($call->inboundUser->patientCareTeamMembers && $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first()) {
                    $obj->billing_provider = $call->inboundUser->patientCareTeamMembers->where('type', 'billing_provider')->first()->member->display_name;
                }
            }
            // nurse info
            $obj->nurse = 'n/a';
            if($call->outboundUser) {
                $obj->nurse = $call->outboundUser->display_name;
            }
            $data[] = $obj;
        }
        $calls = collect($data);

        return Datatables::of($calls)
            ->editColumn('call_date', function($call) {
                return $call->call_date . '<a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon" call-id="'.$call->id.'" column-name="call_date" column-value="'.$call->call_date.'"></span>';
            })
            ->make(true);
    }
    */
}

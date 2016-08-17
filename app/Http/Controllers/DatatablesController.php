<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use Yajra\Datatables\Datatables;
use App\Call;
use Collection;

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

    public function anyDataCalls()
    {
        $calls = Call::with('inboundUser')
            ->with('outboundUser')
            ->with('note')
            ->select(
                [
                    'calls.id',
                    'calls.outbound_cpm_id',
                    'calls.inbound_cpm_id',
                    'calls.call_date',
                    'calls.window_start',
                    'calls.window_end',
                    'notes.type',
                    'notes.body',
                    'calls.note_id',
                    'patient_info.cur_month_activity_time',
                    'patient.display_name AS patient_name'
                ])
            ->leftJoin('patient_info', 'calls.inbound_cpm_id','=','patient_info.user_id')
            ->leftJoin('notes', 'calls.note_id','=','notes.id')
            ->leftJoin('users AS patient', 'calls.inbound_cpm_id','=','patient.ID')
            ->get();

        return Datatables::of($calls)
            ->editColumn('call_date', function($call) {
                return $call->call_date . '<a href="#"><span class="glyphicon glyphicon-edit cpm-editable-icon" call-id="'.$call->id.'" column-name="call_date" column-value="'.$call->call_date.'"></span>';
            })
            ->editColumn('cur_month_activity_time', function($call) {
                if($call->inboundUser && $call->inboundUser->patientInfo) {
                    return $call->inboundUser->patientInfo->currentMonthCCMTime;
                } else {
                    return 'n/a';
                }
            })
            ->addColumn('last_successful_contact_time', function($call) {
                if($call->inboundUser && $call->inboundUser->patientInfo) {
                    return $call->inboundUser->patientInfo->last_successful_contact_time;
                } else {
                    return 'n/a';
                }
            })
            ->make(true);
    }

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
}

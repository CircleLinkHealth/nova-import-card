<?php

namespace App\Http\Controllers;

use App\Call;
use App\Note;
use App\PatientMonthlySummary;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CallsDashboardController extends Controller
{
    public function index()
    {

        return view('admin.CallsDashboard.index');
    }

    public function create(Request $request)
    {

        $note = Note::find($request['noteId']);


        if ($note) {
            $call = $note->call()->first();
            if ($call) {

                return view('admin.CallsDashboard.edit', compact(['note', 'call']));
            }
            $nurses = User::ofType('care-center')->get();
            return view('admin.CallsDashboard.create-call', compact(['note', 'nurses']));
        }

        return back();


    }

    public function edit(Request $request)
    {

        $note   = Note::find($request['noteId']);
        $call   = Call::find($request['callId']);
        $status = $request['status'];
        $date   = new Carbon($call->called_date);


        if ($call->status == $status) {
            $message = 'Call Status not changed.';
        } else {
            $initialStatus = $call->status;
            $call->status  = $status;
            $call->save();

//            $summary = PatientMonthlySummary::where('patient_id', $note->patient_id)
//                                            ->where('month_year', $date->copy()->startOfMonth())
//                                            ->first();

            $summary = PatientMonthlySummary::firstOrCreate([
                'patient_id' => $note->patient_id,
                'month_year' => $date->copy()->startOfMonth()
            ]);

            if ($initialStatus == 'scheduled') {
                if ($status == 'reached') {
                    $summary->no_of_calls            += 1;
                    $summary->no_of_successful_calls += 1;
                } else {
                    $summary->no_of_calls += 1;
                }
                $summary->save();
            } else {
                if ($status == 'reached') {
                    $summary->no_of_successful_calls += 1;
                } else {
                    $summary->no_of_successful_calls -= 1;
                }
                $summary->save();
            }
            $message = 'Call Status successfully changed!';
        }

        return view('admin.CallsDashboard.edit', compact(['note', 'call', 'message']));


    }

    public function createCall(Request $request, NoteService $service)
    {
        $note            = Note::find($request['noteId']);
        $status          = $request['status'];
        $patient         = User::find($note->patient_id);
        $nurse           = User::find($request['nurseId']);
        $phone_direction = $request['direction'];

        $service->storeCallForNote(
            $note,
            $status,
            $patient, $nurse,
            $phone_direction,
            null
        );

        //update monthly summaries
        $date = Carbon::now();
        $summary = PatientMonthlySummary::firstOrCreate([
            'patient_id' => $note->patient_id,
            'month_year' => $date->copy()->startOfMonth()
        ]);

        if ($status == 'reached') {
            $summary->no_of_calls            += 1;
            $summary->no_of_successful_calls += 1;
        } else {
            $summary->no_of_calls += 1;
        }
        $summary->save();



        return redirect()->route('CallsDashboard.index');
    }

    }

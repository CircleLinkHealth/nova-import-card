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

        $note = Note::with(['patient', 'author'])->where('id', $request['noteId'])->first();


        if ($note) {
            $call = $note->call()->first();
            if ($call) {

                return view('admin.CallsDashboard.edit', compact(['note', 'call']));
            }
            $nurses = User::ofType('care-center')->get();
            return view('admin.CallsDashboard.create-call', compact(['note', 'nurses']));
        }
        $message = 'Note Does Not Exist.';
        return redirect()->route('CallsDashboard.index')->with('msg', $message);


    }

    public function edit(Request $request)
    {

        $note   = Note::with(['patient', 'author'])->where('id', $request['noteId'])->first();
        $call   = Call::find($request['callId']);
        $status = $request['status'];
        $date   = new Carbon($call->called_date);


        if ($call->status == $status) {
            $message = 'Call Status Not Changed.';
        } else {
            $initialStatus = $call->status;
            $call->status  = $status;
            $call->save();

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
            $message = 'Call Status Successfully Changed!';
        }


        return redirect()->route('CallsDashboard.create', ['noteId'=> $request['noteId']])->with('msg', $message);



    }

    public function createCall(Request $request, NoteService $service)
    {
        $note            = Note::with(['patient', 'author', 'call'])->where('id', $request['noteId'])->first();
        $call            = $note->call;
        if ($call){
            return view('admin.CallsDashboard.edit', compact(['note', 'call']));
        }

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

        $message = 'Call Successfully Added to Note!';


        return redirect()->route('CallsDashboard.index')->with('msg', $message);

    }

    }

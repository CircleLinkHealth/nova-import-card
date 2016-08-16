<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use Yajra\Datatables\Datatables;
use App\Call;

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
        $calls = Call::with('note')->select(['calls.id', 'calls.call_date', 'calls.window_start', 'calls.window_end', 'notes.type', 'notes.body', 'calls.note_id'])->leftJoin('notes', 'calls.note_id','=','notes.id')->get();
        //$calls = Call::with('note')->select(['calls.*'])->get();

        return Datatables::of($calls)
            /*
            ->editColumn('notes.body', function($call) {
                if($call->note) {
                    return $call->note->body;
                } else {
                    return 'n/a';
                }
            })
            ->editColumn('notes.type', function($call) {
                if($call->note) {
                    return $call->note->type;
                } else {
                    return 'n/a';
                }
            })
            */
            ->make(true);
    }
}

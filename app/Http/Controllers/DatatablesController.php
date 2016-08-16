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
        $calls = Call::with('note')->select(['calls.*'])->get();
        //$calls = Call::with('note')->select(['calls.*'])->get();

        return Datatables::of($calls)
            ->editColumn('body', function($call) {
                if($call->note) {
                    return $call->note->body;
                } else {
                    return '';
                }
            })
            ->editColumn('type', function($call) {
                if($call->note) {
                    return $call->note->type;
                } else {
                    return '';
                }
            })
            ->make(true);
    }
}

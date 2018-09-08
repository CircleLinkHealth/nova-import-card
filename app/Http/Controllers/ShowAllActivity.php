<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Venturecraft\Revisionable\Revision;

class ShowAllActivity extends Controller
{
    /**
     * Show all the activity registered from using CPM.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request)
    {
        if ($request->has('date-from')) {
            $startDate = new Carbon($request['date-from']);
            $endDate   = new Carbon($request['date-to']);
        } else {
            $startDate = Carbon::today()->subWeeks(4);
            $endDate   = Carbon::today();
        }

        //validate input
        $errors = collect();
        if ($endDate->lessThan($startDate)) {
            $errors->push('Invalid date range.');
        }
        if ($endDate->diffInMonths($startDate) > 4) {
            $errors->push('Date range is too large. Please use smaller From and To dates.');
        }

        $startDate->setTime(0, 0);
        $endDate->setTime(23, 59, 59);

        $revisions = collect();
        if ($errors->isEmpty()) {
            $revisions = Revision::where('updated_at', '>=', $startDate->toDateTimeString())
                                 ->where('updated_at', '<=', $endDate->toDateTimeString())
                                 ->orderBy('updated_at', 'desc')
                                 ->paginate(20);
        }

        return view('admin.allActivity.index', compact([
            'errors',
            'startDate',
            'endDate',
            'revisions',
        ]));
    }
}

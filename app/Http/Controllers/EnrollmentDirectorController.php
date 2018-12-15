<?php

namespace App\Http\Controllers;

use App\Enrollee;
use App\Filters\EnrolleeFilters;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentDirectorController extends Controller
{
    public function index()
    {
        return view('admin.ca-director.index');
    }

    public function getEnrollees(Request $request, EnrolleeFilters $filters)
    {

        $fields = ['*'];

        $byColumn  = $request->get('byColumn');
        $query     = $request->get('query');
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $data = Enrollee::filter($filters)->select($fields);

        $count = $data->count();

        $data->limit($limit)
             ->skip($limit * ($page - 1));

        if (isset($orderBy)) {
            $direction = $ascending == 1
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];

    }

    public function getCareAmbassadors()
    {
        $ambassadors = User::ofType('care-ambassador')
                           ->select(['id', 'display_name'])
                           ->get();

        return response()->json($ambassadors->toArray());
    }

    public function assignCareAmbassadorToEnrollees(Request $request)
    {

        Enrollee::find($request->input('enrolleeIds'))->map(function($e) use ($request){
            $e->update(['care_ambassador_id' => $request->input('ambassadorId')]);
        });

        return null;

    }

    public function markEnrolleesAsIneligible(Request $request)
    {
        Enrollee::find($request->input('enrolleeIds'))->map(function($e) use ($request){
            $e->update(['status' => Enrollee::INELIGIBLE]);
        });

        return null;

    }
}

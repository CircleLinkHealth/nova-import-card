<?php

namespace App\Http\Controllers;

use App\Enrollee;
use App\EnrolleeView;
use App\Filters\EnrolleeFilters;
use App\Http\Requests\EditEnrolleeData;
use App\User;
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

        $data = EnrolleeView::filter($filters)->select($fields);

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
        if ( ! $request->input('enrolleeIds')) {
            return response()->json([
                'errors' => 'No enrollee Ids were sent in the request',
            ], 400);
        }
        if ( ! $request->input('ambassadorId')) {
            return response()->json([
                'errors' => 'No ambassador Id was sent in the request',
            ], 400);
        }

        Enrollee::whereIn('id',
            $request->input('enrolleeIds'))->update(['care_ambassador_id' => $request->input('ambassadorId')]);


        return response()->json([], 200);

    }

    public function markEnrolleesAsIneligible(Request $request)
    {
        if ( ! $request->input('enrolleeIds')) {
            return response()->json([
            ], 400);
        }


        Enrollee::whereIn('id', $request->input('enrolleeIds'))->update(['status' => Enrollee::INELIGIBLE]);


        return response()->json([], 200);

    }

    public function editEnrolleeData(EditEnrolleeData $request)
    {
        $phones = $request->input('phones');

        Enrollee::where('id', $request->input('id'))
                ->update([
                    'first_name'          => $request->input('first_name'),
                    'last_name'           => $request->input('last_name'),
                    'lang'                => $request->input('lang'),
                    'status'              => $request->input('status'),
                    'address'             => $request->input('address'),
                    'address_2'           => $request->input('address_2'),
                    'primary_phone'       => $phones['primary_phone'],
                    'home_phone'          => $phones['home_phone'],
                    'other_phone'         => $phones['other_phone'],
                    'cell_phone'          => $phones['cell_phone'],
                    'primary_insurance'   => $request->input('primary_insurance'),
                    'secondary_insurance' => $request->input('secondary_insurance'),
                    'tertiary_insurance'  => $request->input('tertiary_insurance'),
                ]);

        return response()->json([], 200);

    }

    public function addEnrolleeCustomFilter(){
        //make lower case before storing


    }

    public function getPractices(){

    }
}

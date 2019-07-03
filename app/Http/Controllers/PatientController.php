<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 01/07/2019
 * Time: 1:06 PM
 */

namespace App\Http\Controllers;

use App\Filters\PatientListFilters;
use App\User;
use Request;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('patientList');
    }

    public function getPatientList(Request $request, PatientListFilters $filters)
    {
        $fields = ['*'];

        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        //todo
        $data = PatientListView::filter($filters)->select($fields);

        $count = $data->count();

        $data->limit($limit)
             ->skip($limit * ($page - 1));

        if (isset($orderBy)) {
            $direction = 1 == $ascending
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
}

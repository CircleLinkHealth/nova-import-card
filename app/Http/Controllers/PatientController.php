<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 01/07/2019
 * Time: 1:06 PM
 */

namespace App\Http\Controllers;

use App\Filters\PatientListFilters;
use App\PatientAwvSurveyInstanceStatusView;
use App\User;
use Illuminate\Http\Request;

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

        $data = PatientAwvSurveyInstanceStatusView::filter($filters)->select($fields);

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

    public function getPatientContactInfo(Request $request, $userId)
    {
        $user = User::with('phoneNumbers')->findOrFail($userId);

        return response()->json([
            'user_id'       => $userId,
            'phone_numbers' => $user->phoneNumbers,
            'email'         => $user->email,
        ]);
    }

    public function getPatientReport($patienId, $reportType, $year){

        if ($reportType == 'ppp'){
            return redirect()->route('get-ppp-report', [
                'userId' => $patienId,
                'year'   => $year

            ]);
        }

        if ($reportType == 'provider-report'){
            return redirect()->route('get-provider-report', [
                'userId' => $patienId,
                'year'   => $year

            ]);
        }

        throw new \Exception("Report type : [$reportType] does not exist.");

    }
}

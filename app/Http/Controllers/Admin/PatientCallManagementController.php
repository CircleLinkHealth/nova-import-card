<?php namespace App\Http\Controllers\Admin;

use App\Call;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use DateTime;

use Illuminate\Http\Request;

class PatientCallManagementController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$date = new DateTime(date('Y-m-d'));

		// if form submitted dates, override here
		if($request->input('date')) {
			$date = new DateTime($request->input('date') . ' 00:00:01');
		}

		// get all calls for date
		$calls = Call::where('call_date', '=', $date->format('Y-m-d'));

		$calls = $calls->paginate( 20 );

		return view('admin.patientCallManagement.index', compact(['calls', 'date']));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		//
	}

}

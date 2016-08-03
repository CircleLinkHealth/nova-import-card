<?php namespace App\Http\Controllers;

use App\Call;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use DateTime;

use Illuminate\Http\Request;

class PatientCallListController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		// get all calls
		$calls = Call::where('id', '>', 0);

		// filter date
		//$date = new DateTime(date('Y-m-d'));
		$date = 'All';
		if($request->input('date')) {
			if ( strtolower($request->input('date')) != 'all' ) {
				$date = new DateTime($request->input('date') . ' 00:00:01');
				$calls->where('call_date', '=', $date->format('Y-m-d'));
				$date = $date->format('Y-m-d');
			}
		}

		// filter nurse
		$calls->where( 'outbound_cpm_id', '=', \Auth::user()->ID );

		$calls->orderBy('window_start', 'desc');
		$calls = $calls->paginate( 10 );


		return view('patientCallList.index', compact(['calls', 'date']));
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

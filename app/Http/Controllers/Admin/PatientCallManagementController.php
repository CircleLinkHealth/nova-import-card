<?php namespace App\Http\Controllers\Admin;

use App\Call;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use DateTime;
use Auth;

use Illuminate\Http\Request;

class PatientCallManagementController extends Controller {

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
			$date = new DateTime($request->input('date') . ' 00:00:01');
			$calls->where('call_date', '=', $date->format('Y-m-d'));
			$date = $date->format('Y-m-d');
		}

		// filter nurse
		$filterNurse = array();
		if ( !empty($request->input('filterNurse')) ) {
			$filterNurse = $request->input('filterNurse');
			if ( $request->input('filterNurse') != 'all' ) {
				$calls->where( 'outbound_cpm_id', '=', $filterNurse );
			}
		}

		$calls->orderBy('call_date', 'desc');
		$calls = $calls->paginate( 10 );



		// get all nurses
		$nurses = User::with('meta')
			->with('roles')
			->whereHas('roles', function($q) {
				$q->where(function ($query) {
					$query->orWhere('name', 'care-center');
					$query->orWhere('name', 'no-ccm-care-center');
				});
			})
			->pluck('display_name', 'ID');

		return view('admin.patientCallManagement.index', compact(['calls', 'date', 'nurses', 'filterNurse']));
	}

	/**
	 * Show the form for editing an existing resource.
	 *
	 * @return Response
	 */
	public function edit(Request $request, $id)
	{
		if ( !Auth::user()->can( 'users-edit-all' ) ) {
			abort( 403 );
		}
		$messages = \Session::get( 'messages' );

		$params = $request->all();

		$call = Call::find( $id );
		if ( !$call ) {
			return response( "Call not found", 401 );
		}

		// get all nurses
		$nurses = User::with('meta')
			->with('roles')
			->whereHas('roles', function($q) {
				$q->where(function ($query) {
					$query->orWhere('name', 'care-center');
					$query->orWhere('name', 'no-ccm-care-center');
				});
			})
			->pluck('display_name', 'ID');

		return view('admin.patientCallManagement.edit', compact(['call', 'nurses']));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		if ( !Auth::user()->can( 'users-edit-all' ) ) {
			abort( 403 );
		}
		// instantiate user
		$call = Call::find( $id );
		if ( !$call ) {
			return response( "Call not found", 401 );
		}

		// input
		$call->outbound_cpm_id = $request->input('outbound_cpm_id');
		$call->window_start = $request->input('window_start');
		$call->window_end = $request->input('window_end');
		$call->save();

		return redirect()->back()->with( 'messages', ['successfully updated call'] );
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

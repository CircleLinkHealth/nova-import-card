<?php namespace App\Http\Controllers\Admin;

use App\Observation;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ObservationController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// display view
		$observations = Observation::OrderBy('id', 'desc')->limit('100')->get();
		return view('admin.observations.index', [ 'observations' => $observations ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// display view
		return view('admin.observations.create', []);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$params = $request->input();
		$observation = new Observation;
		$observation->msg_id = $params['msg_id'];
		$observation->qtype = $params['qtype'];
		$observation->obs_key = $params['obs_key'];
		$observation->description = $params['description'];
		$observation->icon = $params['icon'];
		$observation->category = $params['category'];
		$observation->save();
		return redirect()->route('admin.observations.edit', [$observation->qid])->with('messages', ['successfully added new observation - '.$params['msg_id']])->send();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		// display view
		$observation = Observation::find($id);
		return view('admin.observations.show', [ 'observation' => $observation, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$observation = Observation::find($id);
		return view('admin.observations.edit', [ 'observation' => $observation, 'messages' => \Session::get('messages') ]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$params = $request->input();
		$observation = Observation::find($id);
		$observation->msg_id = $params['msg_id'];
		$observation->qtype = $params['qtype'];
		$observation->obs_key = $params['obs_key'];
		$observation->description = $params['description'];
		$observation->icon = $params['icon'];
		$observation->category = $params['category'];
		$observation->save();
		return redirect()->back()->with('messages', ['successfully updated observation'])->send();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Observation::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed observation'])->send();
	}

}

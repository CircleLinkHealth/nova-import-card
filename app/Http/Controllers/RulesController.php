<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Rules;
use App\Location;

use App\RulesOperators;
use App\RulesConditions;
use App\RulesActions;

use Illuminate\Http\Request;

class RulesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// display view
		$rules = Rules::orderBy('id', 'desc')->get();
		return view('rules.index', [ 'rules' => $rules ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//$rules = new Rules;
		//dd($rules->getActions(array('Activity' => 'Patient Overview', 'Role' => 'Provider')) );
		return view('rules.create', [
			'operators' => RulesOperators::all(),
			'conditions' => RulesConditions::all(),
			'actions' => RulesActions::all()
		]);
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

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}

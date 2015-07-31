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
			'operators' => RulesOperators::lists('operator', 'id'),
			'conditions' => RulesConditions::lists('condition', 'id'),
			'actions' => RulesActions::lists('action', 'id'),
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
		$rule = Rules::find($id);
		return view('rules.show', [ 'rule' => $rule ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$rule = Rules::find($id);
		if($rule->intrConditions->count()) {
			foreach($rule->intrConditions as $intrCondition) {
				//dd($intrCondition);
			}
		}
		//dd('nope');
		$operators = RulesOperators::lists('operator', 'id');
		//$conditions = RulesConditions::lists('conditions', 'id');

		if($rule) {
			return view('rules.edit', [
				'rule' => $rule,
				'operators' => RulesOperators::lists('operator', 'id'),
				'conditions' => RulesConditions::lists('condition', 'id'),
				'actions' => RulesActions::lists('action', 'id'),
			]);
		} else {
			return response('Rule Not Found', 204);
		}
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

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showMatches(Request $request)
	{
		$params = $request->input();
		$rules = new Rules;
		$ruleActions = $rules->getActions($params, 'ATT');
		return view('rules.showMatches', [ 'params' => $params, 'ruleActions' => $ruleActions ]);
	}

}

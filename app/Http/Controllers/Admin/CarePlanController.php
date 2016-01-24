<?php namespace App\Http\Controllers\Admin;

use App\User;
use App\CarePlan;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CLH\Repositories\CarePlanRepository;
use Symfony\Component\HttpFoundation\ParameterBag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;

class CarePlanController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		// display view
		$careplans = CarePlan::orderBy('id', 'desc');

		// FILTERS
		$params = $request->all();

		// filter user
		$users = User::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');
		$filterUser = 'all';
		if(isset($params['filterUser'])) {
			$filterUser = $params['filterUser'];
			if($params['filterUser'] != 'all') {
				$careplans->where('user_id', '=', $filterUser);
			}
		}

		/*
		// filter pcp
		$pcps = CarePlan::select('section_text')->groupBy('section_text')->get()->lists('section_text', 'section_text');
		$filterPCP = 'all';
		if(!empty($params['filterPCP'])) {
			$filterPCP = $params['filterPCP'];
			if($params['filterPCP'] != 'all') {
				$ucps->whereHas('item', function($q) use ($filterPCP){
					$q->whereHas('pcp', function($qp) use ($filterPCP){
						$qp->where('section_text', '=', $filterPCP);
					});
				});
			}
		}
		*/
		$careplans = $careplans->paginate(10);

		return view('admin.carePlans.index', [ 'careplans' => $careplans, 'users' => $users, 'filterUser' => $filterUser, 'messages' => \Session::get('messages') ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}

		$users = User::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');

		// display view
		return view('admin.carePlans.create', [ 'users' => $users ]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}

		$this->validate($request, [
			'name' => 'required|unique:care_plans,name|max:255',
			'display_name' => 'required',
			'type' => 'required',
		]);

		$carePlanRepo = new CarePlanRepository();
		$params = new ParameterBag($request->input());
		$carePlan = $carePlanRepo->createCarePlan(new CarePlan, $params);

		return redirect()->route('admin.careplans.edit', [$carePlan->id])->with('messages', ['successfully added new care plan -  '.$params->get('display_name')])->send();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		// display view
		$careplan = CarePlan::find($id);
		return view('admin.carePlans.show', [ 'careplan' => $careplan, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		$users = User::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');

		$carePlan = CarePlan::find($id);
		foreach($carePlan->careSections as $careSection) {
			// add parent items to each section
			$careSection->planItems = $carePlan->carePlanItems()
				->where('section_id', '=', $careSection->id)
				->where('parent_id', '=', 0)
				->orderBy('ui_sort', 'asc')
				->with(array('children' => function ($query) {
						$query->orderBy('ui_sort', 'asc');
					}))
				->get();
		}

		$editMode = true;

		return view('admin.carePlans.edit', [ 'editMode' => $editMode, 'carePlan' => $carePlan, 'users' => $users, 'messages' => \Session::get('messages') ]);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}

		$this->validate($request, [
			'name' => 'required',
			'display_name' => 'required',
			'type' => 'required',
		]);

		$carePlan = CarePlan::find($id);
		$carePlanRepo = new CarePlanRepository();
		$params = new ParameterBag($request->input());
		$carePlan = $carePlanRepo->updateCarePlan($carePlan, $params);

		return redirect()->back()->with('messages', ['successfully updated care plan'])->send();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		CarePlan::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed ucp'])->send();
	}












	/**
	 *
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function duplicateCarePlan(Request $request, $id)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		$users = User::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');

		$carePlan = CarePlan::find($id);
		foreach($carePlan->careSections as $careSection) {
			// add parent items to each section
			$careSection->planItems = $carePlan->carePlanItems()
				->where('section_id', '=', $careSection->id)
				->where('parent_id', '=', 0)
				->orderBy('ui_sort', 'asc')
				->with(array('children' => function ($query) {
					$query->orderBy('ui_sort', 'asc');
				}))
				->get();
		}

		return view('admin.carePlans.careplan', [ 'carePlan' => $carePlan, 'users' => $users, 'messages' => \Session::get('messages') ]);
	}

	/**
	 *
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function carePlan($id)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		$users = User::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');

		$carePlan = CarePlan::find($id);
		foreach($carePlan->careSections as $careSection) {
			// add parent items to each section
			$careSection->planItems = $carePlan->carePlanItems()
				->where('section_id', '=', $careSection->id)
				->where('parent_id', '=', 0)
				->orderBy('ui_sort', 'asc')
				->with(array('children' => function ($query) {
					$query->orderBy('ui_sort', 'asc');
				}))
				->get();
		}

		return view('admin.carePlans.careplan', [ 'carePlan' => $carePlan, 'users' => $users, 'messages' => \Session::get('messages') ]);
	}

	/**
	 *
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function carePlanSection($id, $sectionId)
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		$users = User::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');

		$carePlan = CarePlan::find($id);
		foreach($carePlan->careSections as $careSection) {
			// add parent items to each section
			$careSection->planItems = $carePlan->carePlanItems()
				->where('section_id', '=', $careSection->id)
				->where('parent_id', '=', 0)
				->orderBy('ui_sort', 'asc')
				->with(array('careSection' => function ($query) use ($sectionId){
					$query->where('name', '=', $sectionId);
				}))
				->with(array('children' => function ($query) {
					$query->orderBy('ui_sort', 'asc');
				}))
				->get();
		}

		return view('admin.carePlans.careplansection', [ 'carePlan' => $carePlan, 'users' => $users, 'section' => $sectionId, 'messages' => \Session::get('messages') ]);
	}

}

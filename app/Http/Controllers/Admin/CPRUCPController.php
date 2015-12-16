<?php namespace App\Http\Controllers\Admin;

use App\WpUser;
use App\CPRulesUCP;
use App\CPRulesPCP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;

class CPRUCPController extends Controller {

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
		$ucps = CPRulesUCP::orderBy('items_id', 'desc');

		// FILTERS
		$params = $request->all();

		// filter user
		$users = WpUser::whereIn('ID', Auth::user()->viewablePatientIds())->OrderBy('id', 'desc')->get()->lists('fullNameWithId', 'ID');
		$filterUser = 'all';
		if(!empty($params['filterUser'])) {
			$filterUser = $params['filterUser'];
			if($params['filterUser'] != 'all') {
				$ucps->where('user_id', '=', $filterUser);
			}
		}

		// filter pcp
		$pcps = CPRulesPCP::select('section_text')->groupBy('section_text')->get()->lists('section_text', 'section_text');
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

		$ucps = $ucps->paginate(10);

		//dd($pcps);

		return view('admin.ucp.index', [ 'ucps' => $ucps, 'users' => $users, 'filterUser' => $filterUser, 'pcps' => $pcps, 'filterPCP' => $filterPCP ]);
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
		// display view
		return view('admin.ucp.create', []);
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
		$params = $request->input();
		$ucp = new CPRulesUCP;
		$ucp->items_id = $params['items_id'];
		$ucp->user_id = $params['user_id'];
		$ucp->meta_key = $params['meta_key'];
		$ucp->meta_value = $params['meta_value'];
		$ucp->save();
		return redirect()->route('admin.ucp.edit', [$ucp->qid])->with('messages', ['successfully added new ucp - '.$params['msg_id']])->send();
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
		$ucp = CPRulesUCP::find($id);
		return view('admin.ucp.show', [ 'ucp' => $ucp, 'errors' => array(), 'messages' => array() ]);
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
		$ucp = CPRulesUCP::find($id);
		return view('admin.ucp.edit', [ 'ucp' => $ucp, 'messages' => \Session::get('messages') ]);
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
		$params = $request->input();
		$ucp = CPRulesUCP::find($id);
		$ucp->items_id = $params['items_id'];
		$ucp->user_id = $params['user_id'];
		$ucp->meta_key = $params['meta_key'];
		$ucp->meta_value = $params['meta_value'];
		$ucp->save();
		return redirect()->back()->with('messages', ['successfully updated ucp'])->send();
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
		CPRulesUCP::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed ucp'])->send();
	}

}

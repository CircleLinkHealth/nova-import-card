<?php namespace App\Http\Controllers\Admin;

use App\CPRulesUCP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CPRUCPController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// display view
		$ucps = CPRulesUCP::paginate(10);
		return view('admin.ucp.index', [ 'ucps' => $ucps ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
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
		CPRulesUCP::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed ucp'])->send();
	}

}

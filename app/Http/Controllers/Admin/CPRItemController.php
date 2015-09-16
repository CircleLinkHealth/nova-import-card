<?php namespace App\Http\Controllers\Admin;

use App\CPRulesItem;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CPRItemController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// display view
		$items = CPRulesItem::paginate(10);
		return view('admin.items.index', [ 'items' => $items ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// display view
		return view('admin.items.create', []);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$params = $request->input();
		$item = new CPRulesItem;
		$item->msg_id = $params['msg_id'];
		$item->qtype = $params['qtype'];
		$item->obs_key = $params['obs_key'];
		$item->description = $params['description'];
		$item->icon = $params['icon'];
		$item->category = $params['category'];
		$item->save();
		return redirect()->route('admin.items.edit', [$item->qid])->with('messages', ['successfully added new item - '.$params['msg_id']])->send();
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
		$item = CPRulesItem::find($id);
		return view('admin.items.show', [ 'item' => $item, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$item = CPRulesItem::find($id);
		return view('admin.items.edit', [ 'item' => $item, 'messages' => \Session::get('messages') ]);
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
		$item = CPRulesItem::find($id);
		$item->msg_id = $params['msg_id'];
		$item->qtype = $params['qtype'];
		$item->obs_key = $params['obs_key'];
		$item->description = $params['description'];
		$item->icon = $params['icon'];
		$item->category = $params['category'];
		$item->save();
		return redirect()->back()->with('messages', ['successfully updated item'])->send();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		CPRulesItem::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed item'])->send();
	}

}

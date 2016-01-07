<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\CPRulesPCP;
use App\Location;
use App\CPRulesItemMeta;
use App\CPRulesItem;
use App\Http\Controllers\Controller;

use App\WpBlog;
use Illuminate\Http\Request;
use Auth;

class WpBlogController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if(!Auth::user()->can('programs-view')) {
			abort(403);
		}
		// display view
		$wpBlogs = WpBlog::orderBy('blog_id', 'desc')->get();
		return view('admin.wpBlogs.index', [ 'wpBlogs' => $wpBlogs ]);
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

		$messages = \Session::get('messages');

		$locations = Location::where('parent_id', '=', null)->orderBy('id', 'desc')->lists('name', 'id');

		return view('admin.wpBlogs.create', compact([ 'locations', 'errors', 'messages' ]));
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

		// get params
		$params = $request->input();

		$program = new WpBlog;
		$program->location_id = $params['location_id'];
		$program->domain = $params['domain'];
		$program->name = $params['name'];
		$program->display_name = $params['display_name'];
		$program->short_display_name = $params['short_display_name'];
		$program->description = $params['description'];
		$program->site_id = 1;
		$program->path = '/';
		$program->public = 0;
		$program->archived = 0;
		$program->mature = 0;
		$program->spam = 0;
		$program->deleted = 0;
		$program->lang_id = 0;
		$program->save();
		return redirect()->route('admin.programs.edit', ['program' => $program])->with('messages', ['successfully created new program'])->send();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if(!Auth::user()->can('programs-view')) {
			abort(403);
		}
		// display view
		$program = WpBlog::find($id);

		/*
		$cPRulesPCP = CPRulesPCP::where('prov_id', '=', $id)->where('status', '=', 'Active')->with('items.meta')->get();
		if(!empty($cPRulesPCP)) {
			$programItems = array();
			foreach ($cPRulesPCP as $pcp) {
				$programItems[$pcp->pcp_id] = array('section_text' => $pcp->section_text, 'items' => array());
				$cPRulesItems = CPRulesItem::where('pcp_id', '=', $pcp->pcp_id)->where('items_parent', '=', '0')->with('meta', 'question')->get();
				if(!empty($cPRulesItems)) {
					$pcpItems = array();
					foreach($cPRulesItems as $cPItem) {
						// set item and item meta
						$pcpItems[$cPItem->items_id] = $cPItem;
						// get children items, set them and their meta
						$childItems = array();
						$cPRulesChildItems = CPRulesItem::where('pcp_id', '=', $pcp->pcp_id)->where('items_parent', '=', $cPItem->items_id)->with('meta', 'question')->get();
						if(!empty($cPRulesChildItems)) {
							foreach($cPRulesChildItems as $cPChildItem) {
								// set child item and item meta
								$childItems[$cPChildItem->items_id] = $cPChildItem;
							}
						}
						$pcpItems[$cPItem->items_id]['child_items'] = $childItems;
					}
					// add to main array
					$programItems[$pcp->pcp_id]['items'] = $pcpItems;
				}
			}
		}
		*/

		$locations = Location::where('parent_id', '=', null)->orderBy('id', 'desc')->lists('name', 'id');

		return view('admin.wpBlogs.show', compact([ 'program', 'locations', 'errors', 'messages' ]));
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

		$messages = \Session::get('messages');

		$program = WpBlog::find($id);

		$locations = Location::where('parent_id', '=', null)->orderBy('id', 'desc')->lists('name', 'id');

		return view('admin.wpBlogs.edit', compact([ 'program', 'locations', 'errors', 'messages' ]));
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
		// find program
		$program = WpBlog::find($id);
		if(!$program) {
			abort(400);
		}
		// get params
		$params = $request->input();
		$program->location_id = $params['location_id'];
		$program->domain = $params['domain'];
		$program->name = $params['name'];
		$program->display_name = $params['display_name'];
		$program->short_display_name = $params['short_display_name'];
		$program->description = $params['description'];
		$program->save();
		return redirect()->back()->with('messages', ['successfully updated program']);
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
		// find program
		$program = WpBlog::find($id);
		if(!$program) {
			abort(400);
		}

		$program->delete();
		return redirect()->back()->with('messages', ['successfully removed program'])->send();
	}

}

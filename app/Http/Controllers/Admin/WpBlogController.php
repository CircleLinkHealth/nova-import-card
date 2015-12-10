<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\CPRulesPCP;
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
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
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
		if(!Auth::user()->can('programs-view')) {
			abort(403);
		}
		// display view
		$wpBlog = WpBlog::find($id);
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
		return view('admin.wpBlogs.show', [ 'wpBlog' => $wpBlog, 'programItems' => $programItems, 'errors' => array(), 'messages' => array() ]);
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
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
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
		if(!Auth::user()->can('programs-manage')) {
			abort(403);
		}
		//
	}

}

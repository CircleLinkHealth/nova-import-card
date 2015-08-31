<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\CPRulesPCP;
use App\CPRulesItemMeta;
use App\CPRulesItem;
use App\Http\Controllers\Controller;

use App\WpBlog;
use Illuminate\Http\Request;

class WpBlogController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// display view
		$wpBlogs = WpBlog::orderBy('blog_id', 'desc')->get();
		return view('wpBlogs.index', [ 'wpBlogs' => $wpBlogs ]);
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
	public function store()
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
		// display view
		$wpBlog = WpBlog::find($id);
		$cPRulesPCP = CPRulesPCP::where('prov_id', '=', $id)->where('status', '=', 'Active')->get();
		if(!empty($cPRulesPCP)) {
			$programItems = array();
			foreach ($cPRulesPCP as $pcp) {
				$programItems[$pcp->pcp_id] = array('section_text' => $pcp->section_text, 'items' => array());
				$cPRulesItems = CPRulesItem::where('pcp_id', '=', $pcp->pcp_id)->get();
				if(!empty($cPRulesItems)) {
					foreach($cPRulesItems as $cPItem) {
						$programItems[$pcp->pcp_id]['items'][$cPItem->items_id] = $cPItem;
						$programItems[$pcp->pcp_id]['items'][$cPItem->items_id]['meta'] = CPRulesItemMeta::where('items_id', '=', $cPItem->items_id)->get();
					}
				}
			}
		}
		return view('wpBlogs.show', [ 'wpBlog' => $wpBlog, 'programItems' => $programItems, 'errors' => array(), 'messages' => array() ]);
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

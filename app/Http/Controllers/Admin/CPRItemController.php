<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\CPRulesItem;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class CPRItemController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        // display view
        return view('admin.items.create', []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        CPRulesItem::destroy($id);

        return redirect()->back()->with('messages', ['successfully removed item']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $item = CPRulesItem::find($id);

        return view('admin.items.edit', ['item' => $item, 'messages' => \Session::get('messages')]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        // display view
        $items = CPRulesItem::orderBy('items_id', 'desc')->paginate(10);

        return view('admin.items.index', ['items' => $items]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        // display view
        $item = CPRulesItem::find($id);

        return view('admin.items.show', ['item' => $item, 'errors' => [], 'messages' => []]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $params             = $request->input();
        $item               = new CPRulesItem();
        $item->pcp_id       = $params['pcp_id'];
        $item->items_parent = $params['items_parent'];
        $item->qid          = $params['qid'];
        $item->items_text   = $params['items_text'];
        $item->save();

        return redirect()->route('admin.items.edit', [$item->items_id])->with(
            'messages',
            ['successfully added new item - ']
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
        $params             = $request->input();
        $item               = CPRulesItem::find($id);
        $item->pcp_id       = $params['pcp_id'];
        $item->items_parent = $params['items_parent'];
        $item->qid          = $params['qid'];
        $item->items_text   = $params['items_text'];
        $item->save();

        return redirect()->back()->with('messages', ['successfully updated item']);
    }
}

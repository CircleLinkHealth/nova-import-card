<?php namespace App\Http\Controllers\Admin;

use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;

class CPRUCPController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // display view
        $ucps = CPRulesUCP::orderBy('items_id', 'desc');

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::intersectPracticesWith(auth()->user())
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('fullNameWithId', 'id')
            ->all();

        $filterUser = 'all';

        if (!empty($params['filterUser'])) {
            $filterUser = $params['filterUser'];
            if ($params['filterUser'] != 'all') {
                $ucps->where('user_id', '=', $filterUser);
            }
        }

        // filter pcp
        $pcps = CPRulesPCP::select('section_text')->groupBy('section_text')->get()->pluck(
            'section_text',
            'section_text'
        )->all();
        $filterPCP = 'all';
        if (!empty($params['filterPCP'])) {
            $filterPCP = $params['filterPCP'];
            if ($params['filterPCP'] != 'all') {
                $ucps->whereHas('item', function ($q) use ($filterPCP) {
                    $q->whereHas('pcp', function ($qp) use ($filterPCP) {
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

        return redirect()->route('admin.ucp.edit', [$ucp->qid])->with(
            'messages',
            ['successfully added new ucp - ' . $params['msg_id']]
        );
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
        return view('admin.ucp.show', [ 'ucp' => $ucp, 'errors' => [], 'messages' => [] ]);
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

        return redirect()->back()->with('messages', ['successfully updated ucp']);
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

        return redirect()->back()->with('messages', ['successfully removed ucp']);
    }
}

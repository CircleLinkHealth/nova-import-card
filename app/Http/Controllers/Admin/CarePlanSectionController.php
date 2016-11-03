<?php namespace App\Http\Controllers\Admin;

use App\CarePlanSection;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;

class CarePlanSectionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        // display view
        $careplans = CarePlanSection::orderBy('id', 'desc');

        // FILTERS
        $params = $request->all();

        // filter user
        $users = User::intersectPracticesWith(auth()->user())
            ->OrderBy('id', 'desc')
            ->get()
            ->pluck('fullNameWithId', 'id')
            ->all();

        $filterUser = 'all';

        if (isset($params['filterUser'])) {
            $filterUser = $params['filterUser'];
            if ($params['filterUser'] != 'all') {
                $careplans->where('user_id', '=', $filterUser);
            }
        }

        /*
        // filter pcp
        $pcps = CarePlanSection::select('section_text')->groupBy('section_text')->get()->pluck('section_text', 'section_text')->all();
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

        return view('admin.carePlans.index', [
            'careplans'  => $careplans,
            'users'      => $users,
            'filterUser' => $filterUser,
            'messages'   => \Session::get('messages'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }

        $users = User::intersectPracticesWith(auth()->user())
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('fullNameWithId', 'id')
            ->all();

        // display view
        return view('admin.carePlans.create', ['users' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        $params = $request->input();
        $ucp = new CarePlanSection;
        $ucp->name = $params['name'];
        $ucp->display_name = $params['display_name'];
        $ucp->type = $params['type'];
        $ucp->user_id = $params['user_id'];
        $ucp->save();

        return redirect()->route('admin.careplans.edit', [$ucp->id])->with('messages',
            ['successfully added new care plan -  ' . $params['display_name']])->send();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        // display view
        $careplan = CarePlanSection::find($id);

        return view('admin.carePlans.show', [
            'careplan' => $careplan,
            'errors'   => [],
            'messages' => [],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        $users = User::intersectPracticesWith(auth()->user())
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('fullNameWithId', 'id')
            ->all();

        $careplan = CarePlanSection::find($id);

        return view('admin.carePlans.edit', [
            'careplan' => $careplan,
            'users'    => $users,
            'messages' => \Session::get('messages'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update(
        Request $request,
        $id
    ) {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        $params = $request->input();
        $ucp = CarePlanSection::find($id);
        $ucp->name = $params['name'];
        $ucp->display_name = $params['display_name'];
        $ucp->type = $params['type'];
        $ucp->user_id = $params['user_id'];
        $ucp->save();

        return redirect()->back()->with('messages', ['successfully updated care plan'])->send();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        CarePlanSection::destroy($id);

        return redirect()->back()->with('messages', ['successfully removed ucp'])->send();
    }

}

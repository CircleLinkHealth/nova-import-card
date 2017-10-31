<?php namespace App\Http\Controllers\Admin;

use App\CPRulesQuestions;
use App\CPRulesQuestionSets;
use App\Http\Controllers\Controller;
use App\Practice;
use Auth;
use Illuminate\Http\Request;

class CPRQuestionSetController extends Controller
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
        $questionSets = CPRulesQuestionSets::orderBy('qsid', 'desc');

        // FILTERS
        $params = $request->all();

        // filter qsType
        $qsTypes = ['SYM' => 'SYM', 'RPT' => 'RPT', 'HSP' => 'HSP'];
        $filterQsType = 'all';
        if (!empty($params['filterQsType'])) {
            $filterQsType = $params['filterQsType'];
            if ($params['filterQsType'] != 'all') {
                $questionSets->where('qs_type', '=', $filterQsType);
            }
        }

        // filter question
        $questions = CPRulesQuestions::orderBy('qid', 'desc')->get()->pluck('msgIdAndObsKey', 'qid')->all();
        $filterQuestion = 'all';
        if (!empty($params['filterQuestion'])) {
            $filterQuestion = $params['filterQuestion'];
            if ($params['filterQuestion'] != 'all') {
                $questionSets = $questionSets->whereHas('question', function ($q) use ($filterQuestion) {
                    $q->where('qid', '=', $filterQuestion);
                });
            }
        }

        // filter program
        $programs = Practice::orderBy('id', 'desc')->get()->pluck('domain', 'id')->all();
        $filterProgram = 'all';
        if (!empty($params['filterProgram'])) {
            $filterProgram = $params['filterProgram'];
            if ($params['filterProgram'] != 'all') {
                $questionSets->where('provider_id', '=', $filterProgram);
            }
        }

        // finish query
        $questionSets = $questionSets->paginate(10);

        return view('admin.questionSets.index', compact([
            'questionSets',
            'qsTypes',
            'filterQsType',
            'questions',
            'filterQuestion',
            'programs',
            'filterProgram',
        ]));
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
        // display view
        return view('admin.questionSets.create', []);
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
        $questionSet = new CPRulesQuestionSets;
        $questionSet->provider_id = $params['provider_id'];
        $questionSet->qs_type = $params['qs_type'];
        $questionSet->qs_sort = $params['qs_sort'];
        $questionSet->qid = $params['qid'];
        $questionSet->answer_response = $params['answer_response'];
        $questionSet->aid = $params['aid'];
        $questionSet->low = $params['low'];
        $questionSet->high = $params['high'];
        $questionSet->action = $params['action'];
        $questionSet->save();
        return redirect()->route('admin.questionSets.edit', [$questionSet->qsid])->with('messages', ['successfully added new question set'])->send();
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        // display view
        $questionSet = CPRulesQuestionSets::find($id);
        return view('admin.questionSets.show', ['questionSet' => $questionSet, 'errors' => [], 'messages' => []]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        $questionSet = CPRulesQuestionSets::find($id);
        $programs = Practice::get();
        if (!empty($questionSet->rulesItems)) {
            foreach ($questionSet->rulesItems as $item) {
                if (isset($item->pcp->program->first()->domain)) {
                    $programItems[] = $item->pcp->program->first()->domain;
                }
            }
        }
        return view('admin.questionSets.edit', [ 'questionSet' => $questionSet, 'programs' => $programs, 'messages' => \Session::get('messages') ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        $params = $request->input();
        $questionSet = CPRulesQuestionSets::find($id);
        if (!$questionSet) {
            return redirect()->back()->with('messages', ['could not find question set'.$id])->send();
        }
        $questionSet->provider_id = $params['provider_id'];
        $questionSet->qs_type = $params['qs_type'];
        $questionSet->qs_sort = $params['qs_sort'];
        $questionSet->qid = $params['qid'];
        $questionSet->answer_response = $params['answer_response'];
        if (empty($params['answer_response'])) {
            $questionSet->answer_response = null;
        }
        $questionSet->aid = $params['aid'];
        if (empty($params['aid'])) {
            $questionSet->aid = null;
        }
        $questionSet->low = $params['low'];
        $questionSet->high = $params['high'];
        $questionSet->action = $params['action'];
        $questionSet->save();
        return redirect()->back()->with('messages', ['successfully updated question set'])->send();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('programs-manage')) {
            abort(403);
        }
        CPRulesQuestionSets::destroy($id);
        return redirect()->back()->with('messages', ['successfully removed questionSet'])->send();
    }
}

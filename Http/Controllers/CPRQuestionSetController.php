<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use App\CPRulesQuestions;
use App\CPRulesQuestionSets;
use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Http\Request;

class CPRQuestionSetController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // display view
        return view('admin.questionSets.create', []);
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
        CPRulesQuestionSets::destroy($id);

        return redirect()->back()->with('messages', ['successfully removed questionSet']);
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
        $questionSet = CPRulesQuestionSets::find($id);
        $programs    = Practice::get();
        if ( ! empty($questionSet->rulesItems)) {
            foreach ($questionSet->rulesItems as $item) {
                if (isset($item->pcp->program->first()->domain)) {
                    $programItems[] = $item->pcp->program->first()->domain;
                }
            }
        }

        return view('admin.questionSets.edit', ['questionSet' => $questionSet, 'programs' => $programs, 'messages' => \Session::get('messages')]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // display view
        $questionSets = CPRulesQuestionSets::orderBy('qsid', 'desc');

        // FILTERS
        $params = $request->all();

        // filter qsType
        $qsTypes      = ['SYM' => 'SYM', 'RPT' => 'RPT', 'HSP' => 'HSP'];
        $filterQsType = 'all';
        if ( ! empty($params['filterQsType'])) {
            $filterQsType = $params['filterQsType'];
            if ('all' != $params['filterQsType']) {
                $questionSets->where('qs_type', '=', $filterQsType);
            }
        }

        // filter question
        $questions      = CPRulesQuestions::orderBy('qid', 'desc')->get()->pluck('msgIdAndObsKey', 'qid')->all();
        $filterQuestion = 'all';
        if ( ! empty($params['filterQuestion'])) {
            $filterQuestion = $params['filterQuestion'];
            if ('all' != $params['filterQuestion']) {
                $questionSets = $questionSets->whereHas('question', function ($q) use ($filterQuestion) {
                    $q->where('qid', '=', $filterQuestion);
                });
            }
        }

        // filter program
        $programs      = Practice::orderBy('id', 'desc')->get()->pluck('domain', 'id')->all();
        $filterProgram = 'all';
        if ( ! empty($params['filterProgram'])) {
            $filterProgram = $params['filterProgram'];
            if ('all' != $params['filterProgram']) {
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
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        // display view
        $questionSet = CPRulesQuestionSets::find($id);

        return view('admin.questionSets.show', ['questionSet' => $questionSet, 'errors' => [], 'messages' => []]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params                       = $request->input();
        $questionSet                  = new CPRulesQuestionSets();
        $questionSet->provider_id     = $params['provider_id'];
        $questionSet->qs_type         = $params['qs_type'];
        $questionSet->qs_sort         = $params['qs_sort'];
        $questionSet->qid             = $params['qid'];
        $questionSet->answer_response = $params['answer_response'];
        $questionSet->aid             = $params['aid'];
        $questionSet->low             = $params['low'];
        $questionSet->high            = $params['high'];
        $questionSet->action          = $params['action'];
        $questionSet->save();

        return redirect()->route('admin.questionSets.edit', [$questionSet->qsid])->with(
            'messages',
            ['successfully added new question set']
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
        $params      = $request->input();
        $questionSet = CPRulesQuestionSets::find($id);
        if ( ! $questionSet) {
            return redirect()->back()->with('messages', ['could not find question set'.$id]);
        }
        $questionSet->provider_id     = $params['provider_id'];
        $questionSet->qs_type         = $params['qs_type'];
        $questionSet->qs_sort         = $params['qs_sort'];
        $questionSet->qid             = $params['qid'];
        $questionSet->answer_response = $params['answer_response'];
        if (empty($params['answer_response'])) {
            $questionSet->answer_response = null;
        }
        $questionSet->aid = $params['aid'];
        if (empty($params['aid'])) {
            $questionSet->aid = null;
        }
        $questionSet->low    = $params['low'];
        $questionSet->high   = $params['high'];
        $questionSet->action = $params['action'];
        $questionSet->save();

        return redirect()->back()->with('messages', ['successfully updated question set']);
    }
}

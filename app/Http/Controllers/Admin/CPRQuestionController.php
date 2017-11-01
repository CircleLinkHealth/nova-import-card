<?php namespace App\Http\Controllers\Admin;

use App\CPRulesQuestions;
use App\Http\Controllers\Controller;
use App\Practice;
use Auth;
use Illuminate\Http\Request;

class CPRQuestionController extends Controller
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
        $questions = CPRulesQuestions::orderBy('qid', 'desc')->paginate(10);
        return view('admin.questions.index', ['questions' => $questions]);
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
        return view('admin.questions.create', []);
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
        $question = new CPRulesQuestions;
        $question->msg_id = $params['msg_id'];
        $question->qtype = $params['qtype'];
        $question->obs_key = $params['obs_key'];
        $question->description = $params['description'];
        $question->icon = $params['icon'];
        $question->category = $params['category'];
        $question->save();
        return redirect()->route('admin.questions.edit', [$question->qid])->with('messages', ['successfully added new question - ' . $params['msg_id']])->send();
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
        $question = CPRulesQuestions::find($id);
        return view('admin.questions.show', ['question' => $question, 'errors' => array(), 'messages' => array()]);
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
        $question = CPRulesQuestions::find($id);
        $programs = Practice::get();
        if (!empty($question->rulesItems)) {
            foreach ($question->rulesItems as $item) {
                if (isset($item->pcp->program->first()->domain)) {
                    $programItems[] = $item->pcp->program->first()->domain;
                }
            }
        }
        return view('admin.questions.edit', [ 'question' => $question, 'programs' => $programs, 'messages' => \Session::get('messages') ]);
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
        $question = CPRulesQuestions::find($id);
        $question->msg_id = $params['msg_id'];
        $question->qtype = $params['qtype'];
        $question->obs_key = $params['obs_key'];
        $question->description = $params['description'];
        $question->icon = $params['icon'];
        $question->category = $params['category'];
        $question->save();
        return redirect()->back()->with('messages', ['successfully updated question'])->send();
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
        CPRulesQuestions::destroy($id);
        return redirect()->back()->with('messages', ['successfully removed question'])->send();
    }
}

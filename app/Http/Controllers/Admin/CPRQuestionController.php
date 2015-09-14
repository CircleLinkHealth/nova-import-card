<?php namespace App\Http\Controllers\Admin;

use App\CPRulesQuestions;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CPRQuestionController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// display view
		$questions = CPRulesQuestions::paginate(10);
		return view('admin.questions.index', [ 'questions' => $questions ]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
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
		$params = $request->input();
		$question = new CPRulesQuestions;
		$question->msg_id = $params['msg_id'];
		$question->qtype = $params['qtype'];
		$question->obs_key = $params['obs_key'];
		$question->description = $params['description'];
		$question->icon = $params['icon'];
		$question->category = $params['category'];
		$question->save();
		return redirect()->route('admin.questions.edit', [$question->qid])->with('messages', ['successfully added new question - '.$params['msg_id']])->send();
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
		$question = CPRulesQuestions::find($id);
		return view('admin.questions.show', [ 'question' => $question, 'errors' => array(), 'messages' => array() ]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$question = CPRulesQuestions::find($id);
		return view('admin.questions.edit', [ 'question' => $question, 'messages' => \Session::get('messages') ]);
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
		CPRulesQuestions::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed question'])->send();
	}

}

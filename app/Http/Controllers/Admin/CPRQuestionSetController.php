<?php namespace App\Http\Controllers\Admin;

use App\CPRulesQuestions;
use App\CPRulesQuestionSets;
use App\WpBlog;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CPRQuestionSetController extends Controller
{

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// display view
		$questionSets = CPRulesQuestionSets::orderBy('qid', 'desc');

		// FILTERS
		$params = $request->all();

		// filter user
		$qsTypes = array('SYM', 'RPT', 'HSP');
		$filterQsType = 'all';
		if(!empty($params['filterUser'])) {
			$filterUser = $params['filterUser'];
			if($params['filterUser'] != 'all') {
				$questionSets = $questionSets->whereHas('user', function($q) use ($filterUser){
					$q->where('ID', '=', $filterUser);
				});
			}
		}

		// finish query
		$questionSets = $questionSets->paginate(10);

		return view('admin.questionSets.index', ['questionSets' => $questionSets]);
	}

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
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$params = $request->input();
		$questionSet = new CPRulesQuestionSets;
		$questionSet->msg_id = $params['msg_id'];
		$questionSet->qtype = $params['qtype'];
		$questionSet->obs_key = $params['obs_key'];
		$questionSet->description = $params['description'];
		$questionSet->icon = $params['icon'];
		$questionSet->category = $params['category'];
		$questionSet->save();
		return redirect()->route('admin.questionSets.edit', [$questionSet->qid])->with('messages', ['successfully added new questionSet - ' . $params['msg_id']])->send();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function show($id)
	{
		// display view
		$questionSet = CPRulesQuestionSets::find($id);
		return view('admin.questionSets.show', ['questionSet' => $questionSet, 'errors' => array(), 'messages' => array()]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function edit($id)
	{
		$questionSet = CPRulesQuestionSets::find($id);
		$programs = WpBlog::get();
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
		$params = $request->input();
		$questionSet = CPRulesQuestionSets::find($id);
		$questionSet->msg_id = $params['msg_id'];
		$questionSet->qtype = $params['qtype'];
		$questionSet->obs_key = $params['obs_key'];
		$questionSet->description = $params['description'];
		$questionSet->icon = $params['icon'];
		$questionSet->category = $params['category'];
		$questionSet->save();
		return redirect()->back()->with('messages', ['successfully updated questionSet'])->send();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		CPRulesQuestionSets::destroy($id);
		return redirect()->back()->with('messages', ['successfully removed questionSet'])->send();
	}

}

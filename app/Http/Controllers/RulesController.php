<?php namespace App\Http\Controllers;

use App\Rules;
use App\RulesActions;
use App\RulesConditions;
use App\RulesIntrActions;
use App\RulesIntrConditions;
use App\RulesOperators;
use App\Services\RulesService;
use Illuminate\Http\Request;

class RulesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // display view
        $rules = Rules::orderBy('id', 'desc')->paginate(50);
        return view('rules.index', [ 'rules' => $rules ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //$rules = new Rules;
        //dd($rules->getActions(array('Activity' => 'Patient Overview', 'Role' => 'Provider')) );
        return view('rules.create', [
            'operators'  => RulesOperators::pluck('operator', 'id')->all(),
            'conditions' => RulesConditions::pluck('condition', 'id')->all(),
            'actions'    => RulesActions::pluck('action', 'id')->all(),
            'messages'   => \Session::get('messages')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $params = $request->input();
        $rule = new Rules;
        $rule->rule_name = $params['rule_name'];
        $rule->rule_description = $params['rule_description'];
        $rule->active = $params['active'];
        $rule->type_id = $params['type_id'];
        $rule->sort = $params['sort'];
        $rule->approve = $params['approve'];
        $rule->archive = $params['archive'];
        $rule->summary = $params['summary'];
        $rule->save();
        // build conditions from inputs
        if (!empty($params['conditions'])) {
            $newConditions = array();
            $affectedRows = RulesIntrConditions::where('rule_id', '=', $rule->id)->delete();
            foreach ($params['conditions'] as $key => $formKey) {
                if (strlen($formKey) < 3) {
                    $newCondition = new RulesIntrConditions();
                    $newCondition->operator_id = $params['c'.$formKey.'operator'];
                    $newCondition->rule_id = $rule->id;
                    $newCondition->condition_id = $params['c'.$formKey.'condition'];
                    $newCondition->value = $params['c'.$formKey.'value'];
                    $newConditions[] = $newCondition;
                }
            }
            $rule->intrConditions()->saveMany($newConditions);
        }
        // build conditions from inputs
        if (!empty($params['actions'])) {
            $newActions = array();
            $affectedRows = RulesIntrActions::where('rule_id', '=', $rule->id)->delete();
            foreach ($params['actions'] as $key => $formKey) {
                if (strlen($formKey) < 3) {
                    $newAction = new RulesIntrActions();
                    $newAction->operator_id = $params['a'.$formKey.'operator'];
                    $newAction->rule_id = $rule->id;
                    $newAction->action_id = $params['a'.$formKey.'action'];
                    $newAction->value = $params['a'.$formKey.'value'];
                    $newActions[] = $newAction;
                }
            }
            $rule->intrActions()->saveMany($newActions);
        }
        $rule->push();
        return redirect()->route('admin.rules.edit', [$rule->id])->with('messages', ['successfully added new rule - '.$params['rule_name']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $rule = Rules::find($id);
        return redirect()->route('admin.rules.edit', [$rule->id]);
        return view('rules.show', [ 'rule' => $rule ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $rule = Rules::find($id);
        if ($rule->intrConditions->count()) {
            foreach ($rule->intrConditions as $intrCondition) {
                //dd($intrCondition);
            }
        }
        //dd('nope');
        $operators = RulesOperators::pluck('operator', 'id')->all();
        //$conditions = RulesConditions::pluck('conditions', 'id')->all();

        if ($rule) {
            return view('rules.edit', [
                'rule'       => $rule,
                'operators'  => RulesOperators::pluck('operator', 'id')->all(),
                'conditions' => RulesConditions::pluck('condition', 'id')->all(),
                'actions'    => RulesActions::pluck('action', 'id')->all(),
                'messages'   => \Session::get('messages')
            ]);
        } else {
            return response('Rule Not Found', 204);
        }
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
        $rule = Rules::with('intrConditions', 'intrActions')->find($id);
        // build conditions from inputs
        if (!empty($params['conditions'])) {
            $newConditions = array();
            $affectedRows = RulesIntrConditions::where('rule_id', '=', $rule->id)->delete();
            foreach ($params['conditions'] as $key => $formKey) {
                if (strlen($formKey) < 3) {
                    $newCondition = new RulesIntrConditions();
                    $newCondition->operator_id = $params['c'.$formKey.'operator'];
                    $newCondition->rule_id = $rule->id;
                    $newCondition->condition_id = $params['c'.$formKey.'condition'];
                    $newCondition->value = $params['c'.$formKey.'value'];
                    $newConditions[] = $newCondition;
                }
            }
            $rule->intrConditions()->saveMany($newConditions);
        }
        // build conditions from inputs
        if (!empty($params['actions'])) {
            $newActions = array();
            $affectedRows = RulesIntrActions::where('rule_id', '=', $rule->id)->delete();
            foreach ($params['actions'] as $key => $formKey) {
                if (strlen($formKey) < 3) {
                    $newAction = new RulesIntrActions();
                    $newAction->operator_id = $params['a'.$formKey.'operator'];
                    $newAction->rule_id = $rule->id;
                    $newAction->action_id = $params['a'.$formKey.'action'];
                    $newAction->value = $params['a'.$formKey.'value'];
                    $newActions[] = $newAction;
                }
            }
            $rule->intrActions()->saveMany($newActions);
        }
        $rule->rule_name = $params['rule_name'];
        $rule->rule_description = $params['rule_description'];
        $rule->active = $params['active'];
        $rule->type_id = $params['type_id'];
        $rule->sort = $params['sort'];
        $rule->approve = $params['approve'];
        $rule->archive = $params['archive'];
        $rule->summary = $params['summary'];
        $rule->push();
        return redirect()->back()->with('messages', ['successfully updated rule']);
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function showMatches(Request $request)
    {
        $params = $request->input();
        $rulesService = new RulesService;
        $ruleActions = $rulesService->getActions($params, 'ATT');
        return view('rules.showMatches', [ 'params' => $params, 'ruleActions' => $ruleActions ]);
    }
}

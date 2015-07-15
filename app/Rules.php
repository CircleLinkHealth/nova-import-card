<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Rules extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    public function intrConditions()
    {
        return $this->hasMany('App\RulesIntrConditions', 'rule_id');
    }

    public function intrActions()
    {
        return $this->hasMany('App\RulesIntrActions', 'rule_id');
    }

    public function getActions($params, $type = 'ATT')
    {
        if(empty($params)) {
            return false;
        }

        // build query string
        $sql = "select r.id,r.rule_name, r.active, r.type_id, r.sort
            from lv_rules r

            where r.type_id = 'ATT' AND r.approve = 'Y' AND r.archive = 'N'";

        // add params to quesy string
        if(!empty($params)) {
            foreach( $params as $key => $value ) {
                $sql .= "AND r.id in (
        select rule_id from lv_rules_intr_conditions where
                value IN ('".$value."')
                and condition_id = (select id from lv_rules_conditions where
                condition_name = '".$key."'))";
            }
        }

        // query sql for rules
        $rules = \DB::select( \DB::raw($sql) );
        if(empty($rules)) {
            return false;
        }

        // use rule_id from above to get actions
        if(isset($rules[0]->id)) {
            $rule_id = $rules[0]->id;
            $actions = \DB::select( \DB::raw("select r.id, r.rule_name, r.active, r.type_id, r.sort,
                a.action_name, oa.operator_description, ira.value
                from lv_rules r
                left join lv_rules_intr_actions ira on ira.rule_id = r.id
                left join lv_rules_actions a on a.id = ira.action_id
                left join lv_rules_operators oa on oa.id = ira.operator_id

                where r.type_id = '".$type."' AND r.approve = 'Y' AND r.archive = 'N'
                    AND r.id = ".$rule_id) );
            if(!empty($actions)) {
                return $actions;
            } else {
                return false;
            }
        }
    }

}

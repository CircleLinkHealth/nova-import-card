<?php namespace App\Services;

use App\Rules;
use App\Http\Requests;
use App\User;
use App\Observation;
use App\UserMeta;
use App\Comment;
use DB;
use Validator;

class RulesService
{

    public function getActions($params, $type = 'ATT')
    {
        if (empty($params)) {
            return false;
        }

        // build query string
        $sql = "select r.id,r.rule_name, r.active, r.type_id, r.sort
            from lv_rules r

            where r.type_id = 'ATT' AND r.approve = 'Y' AND r.archive = 'N'";

        // add params to query string
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $quotedValue = "";
                if ($value == '') {
                    $quotedValue = "'%0000000000000%'"; // hack to force blank params to break finding a rule
                } else {
                    // if contains a bracket, assuming its an array
                    if (strpos($value, '[') !== false) {
                        // remove brackets now that we know
                        $value = str_replace(array('[', ']'), "", $value);
                        // if comma, assume comma delimited
                        if (strpos($value, ',') !== false) {
                            $valueArgs = explode(',', $value);
                            if (!empty($valueArgs)) {
                                foreach ($valueArgs as $valueArg) {
                                    $valueArg = trim($valueArg);
                                    $valueArg = str_replace(array("'", '"'), "", $valueArg);
                                    $quotedValue .= "'" . $valueArg . "', ";
                                }
                                // remove trailing comma
                                $quotedValue = trim($quotedValue);
                                $quotedValue = rtrim($quotedValue, ",");
                            }
                        } else {
                            $quotedValue = "'" . $value . "'";
                        }
                    } else {
                        $quotedValue = "'%" . $value . "%'";
                    }
                }
                $sql .= "AND r.id in (
        select rule_id from lv_rules_intr_conditions where
                value LIKE (".$quotedValue.")
                and condition_id = (select id from lv_rules_conditions where
                condition_name = '".$key."'))";
            }
        }

        $sql = str_replace(["\n","\r"], "", $sql);

        // query sql for rules
        $rules = \DB::select(\DB::raw($sql));
        if (empty($rules)) {
            return false;
        }

        // use rule_id from above to get actions
        if (isset($rules[0]->id)) {
            $rule_id = $rules[0]->id;
            $actions = \DB::select(\DB::raw("select r.id, r.rule_name, r.active, r.type_id, r.sort,
                a.action_name, oa.operator_description, ira.value
                from lv_rules r
                left join lv_rules_intr_actions ira on ira.rule_id = r.id
                left join lv_rules_actions a on a.id = ira.action_id
                left join lv_rules_operators oa on oa.id = ira.operator_id

                where r.type_id = '".$type."' AND r.approve = 'Y' AND r.archive = 'N'
                    AND r.id = ".$rule_id));
            if (!empty($actions)) {
                return $actions;
            } else {
                return false;
            }
        }
    }
}

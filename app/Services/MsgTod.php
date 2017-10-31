<?php namespace App\Services;

use DB;

/*
$this->_ci->load->model('cpm_1_7_rules_tod_model', 'tod');
*/

class MsgTod
{

    public function __construct()
    {
    }


    // get list of available Diagnosis for user
    public function getNextTod($prov_id, $user_id)
    {
        $strReturn = '';

        // Get list of Diagnosis groups that user is active in.
        $arrPickList = $this->getActiveList($prov_id, $user_id);

        // select message group at random from above list
        if (!empty($arrPickList)) {
            $strCat = $arrPickList[array_rand($arrPickList)];
        }

        // echo "<br>TOD_Library<br>";
        // print_r($strCat);

        // if no message group found, there is nothing to send
        // if(!empty($strCat)) {
        if (isset($strCat->items_parent)) {
            // find last message sent for this category

            // print_r($this->_ci->tod->getLastMsg($strCat->items_parent, $user_id));
            $makeobj = (object) $this->getLastMsg($strCat->items_parent, $user_id);
            $intSeqNum = get_object_vars($makeobj);

            // select next record
            if (empty($intSeqNum)) {
                $arrReturn = $this->getNext($strCat->items_parent);
            } else {
                $arrReturn = $this->getNext($strCat->items_parent, $intSeqNum['meta_value']);
            }

            // if last question was last asked, start over again
            if (empty($arrReturn)) {
                $arrReturn = $this->getNext($strCat->items_parent);
            }

            // if sequence not found create record and start from first message
            if (!empty($arrReturn)) {
                // print_r($arrReturn);
                if (empty($intSeqNum)) {
                    $this->insertNew($strCat->items_parent, $user_id, $arrReturn->qs_sort);
                } else {
                    // update ucp record
                    $this->updateTod($strCat->items_parent, $user_id, $arrReturn->qs_sort);
                }
                // return msg_id
                $strReturn = $arrReturn->msg_id;
            }
        }

        return $strReturn;
    }//getNextTod








    public function getActiveList($prov_id, $user_id)
    {
        /**
         *
         *  @param    prov_id: provider id
         *            user_id: user id
         *  @return   array of questions
         *
         */

        $sql = <<<query
select distinct i.items_parent
from rules_question_sets qs
join rules_questions q on q.qid = qs.qid
join rules_items i on i.qid = qs.qid
join rules_ucp u on u.items_id = i.items_parent
where qs.provider_id = {$prov_id}
and u.user_id = {$user_id}
and u.meta_key = 'status'
and u.meta_value = 'Active'
and qs.qs_type = 'TOD'
query;

// echo $query;
        $results = DB::connection('mysql_no_prefix')->select(DB::raw($sql));

        return $results;
    }//getActiveList


    public function getLastMsg($intItemID, $user_id)
    {
        /**
         *
         * @param    inItemID : Item id for category
         *  @param    user_id: user id
         *
         * @return   last message id for this category
         *
         */

        $arrReturn = [];

        if (!empty($intItemID) and !empty($user_id)) {
            $sql = <<<query
select meta_value
from rules_ucp
where items_id = {$intItemID}
and user_id = {$user_id}
and meta_key = 'TOD'
LIMIT 1
query;

            // echo $query;

            $results = DB::connection('mysql_no_prefix')->select(DB::raw($sql));
            if (isset($results[0])) {
                $arrReturn = $results[0];
            }
        }

        return $arrReturn;
    }//getLastMsg

    public function getNext($strCat, $intSeqNum = 0)
    {
        $arrReturn = [];

        if (!empty($strCat)) {
            $sql = <<<query
select qs.qs_sort, q.msg_id
from rules_items i
join rules_question_sets qs on qs.qid = i.qid
join rules_questions q on q.qid = i.qid
where i.items_parent = {$strCat}
and i.qid > 0
and qs.qs_sort > {$intSeqNum}
order by qs.qs_sort
limit 1
query;

// echo query;

            $results = DB::connection('mysql_no_prefix')->select(DB::raw($sql));
            if (isset($results[0])) {
                $arrReturn = $results[0];
            }
        }
        return $arrReturn;
    }//getNext

    public function insertNew($strCat, $user_id, $qssort)
    {

        if (!empty($strCat) and !empty($user_id) and !empty($qssort)) {
            $data = [
                'items_id' => $strCat,
                'user_id' => $user_id,
                'meta_key' => 'TOD',
                'meta_value' => $qssort];

// echo query;
            $obs_id = DB::connection('mysql_no_prefix')->table('rules_ucp')->insertGetId($data);
            echo "<br>MsgTod->insertNew() rules_ucp.id#=" . $user_id;
        }
        return;
    }// insertNew

    public function updateTod($strCat, $user_id, $qssort)
    {

        if (!empty($strCat) and !empty($user_id) and !empty($qssort)) {
            /*
            $query = <<<query
            update rules_ucp
            set meta_value = {$qssort}
            where items_id = {$strCat}
            and user_id = {$user_id}
            and meta_key = 'TOD'
            query;
            */
            // echo query;
            $result = DB::connection('mysql_no_prefix')->table('rules_ucp')
                ->where('items_id', '=', $strCat)
                ->where('user_id', '=', $user_id)
                ->where('meta_key', '=', 'TOD')
                ->update(['meta_value' => $qssort]);
            echo "<br>MsgTod->updateTod() updated , items_id=$user_id, items_id=$strCat, meta_value=$qssort";
        }
        return;
    }//updateTod
}

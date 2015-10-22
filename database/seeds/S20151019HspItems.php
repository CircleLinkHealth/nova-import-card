<?php

use App\CPRulesPCP;
use App\CPRulesUCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\CPRulesQuestions;
use App\CPRulesQuestionSets;
use App\CPRulesAnswers;
use App\Observation;
use App\ObservationMeta;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151019HspItems extends Seeder {

    /*
     * CF_DM_HSP_10
     * CF_HSP_10
     * CF_HSP_20
     * CF_HSP_30
     * CF_HSP_EX_01
     */
    public function run()
    {

        $programIds = array(7, 8, 9, 10, 11, 12);

        $newItems = array(
            'CF_HSP_10' => array(
                'items_text' => 'Track Care Transitions', // Y / N
                'APP_EN' => 'Are you still in the hospital or ER?',
                'APP_ES' => 'Are you still in the hospital or ER?',
                'ui_default' => 'Inactive',
                'ui_sort' => '1',
                'ui_fld_type' => 'SELECT',
                'child1' => array(
                    'items_text' => 'Contact Days',
                    'APP_EN' => 'Are you still in the hospital or ER?',
                    'APP_ES' => 'Are you still in the hospital or ER?',
                    'ui_default' => '',
                    'ui_sort' => '1',
                    'ui_show_detail' => '0',
                    'radio_options' => '{"M,W,F": "1,3,5", "Weekly (Friday)": "5"}',
                    'ui_fld_select' => '',
                    'ui_fld_type' => 'RADIO',
                ),
            ),
            'CF_HSP_20' => array(
                'items_text' => 'HSP Visit Type', // ER / HSP
                'APP_EN' => 'Which best describes your visit?',
                'APP_ES' => 'Which best describes your visit?',
            ),
            'CF_HSP_30' => array(
                'items_text' => 'HSP Visit Type', // DATE
                'APP_EN' => 'What date were you discharged?',
                'APP_ES' => 'What date were you discharged?',
            ),
            'CF_HSP_EX_01' => array(
                'items_text' => 'HSP Thank you and goodbye', // exit
                'APP_EN' => 'Thank you. Your provider is being informed.',
                'APP_ES' => 'Thank you. Your provider is being informed.',
            ),
            /*
            'CF_HSP_20' => array(
                'items_text' => 'HSP Visit Type', // ER / HSP
                'APP_EN' => 'Which best describes your visit?',
                'APP_ES' => 'Which best describes your visit?',
            ),
            'CF_HSP_30' => array(
                'items_text' => 'HSP Date Discharged', // MM/DD/YYYY
                'APP_EN' => 'Date Discharged',
                'APP_ES' => 'Date Discharged',
            ),
            */
        );

        // firstly set answer vars and add new answers if they dont already exist
        $answer = CPRulesAnswers::where('value', '=', 'ER')
            ->first();
        if(!$answer) {
            $newAnswer = new CPRulesAnswers;
            $newAnswer->value = 'ER';
            $newAnswer->alt_answers = 'HSP';
            $newAnswer->a_sort = '1';
            $newAnswer->save();
        }
        /*
        $answer = CPRulesAnswers::where('value', '=', 'HSP')
            ->first();
        if(!$answer) {
            $newAnswer = new CPRulesAnswers;
            $newAnswer->value = 'HSP';
            $newAnswer->alt_answers = '';
            $newAnswer->a_sort = '1';
            $newAnswer->save();
        }
        */






        foreach($programIds as $programId) {
            echo PHP_EOL."Program $programId".PHP_EOL.PHP_EOL;
            // first remove all existing items
            /*
            $items = CPRulesItem::whereHas('pcp', function ($q) use ($programId) {
                $q->where('section_text', '=', 'Transitional Care Management');
                $q->where('prov_id', '=', $programId);
            })
                ->get();
            foreach($items as $item) {
                echo PHP_EOL."-----------------".PHP_EOL.PHP_EOL;
                echo $item->items_id.'-'.$item->items_text.PHP_EOL;
                if($item->question) {
                    echo 'qid='.$item->question->qid . PHP_EOL;
                    echo 'msgid='.$item->question->msg_id . PHP_EOL;
                }

                // delete item
                $items = CPRulesItem::where('items_id', '=', $item->items_id)->delete();

                // delete itemmeta
                $items = CPRulesItemMeta::where('items_id', '=', $item->items_id)->delete();

                // delete all UCP for this item
                $items = CPRulesUCP::where('items_id', '=', $item->items_id)->delete();
            }
            */



            // get pcp section for program
            $pcp = CPRulesPCP::where('section_text', '=', 'Transitional Care Management')
                ->where('prov_id', '=', $programId)
                ->first();
            if(!$pcp) {
                echo "no pcp id found for $programId, continue 1".PHP_EOL;
                continue 1;
            }
            $pcpId = $pcp->pcp_id;

            // add new items
            foreach ($newItems as $msgId => $itemInfo) {
                $question = CPRulesQuestions::where('msg_id', '=', $msgId)->first();
                if($question) {

                    // update questionSet
                    $questionSets = CPRulesQuestionSets::where('qid', '=', $question->qid)
                        ->where('provider_id', '=', $programId)
                        ->get();
                    echo "start questionset updates for qid ".$question->qid.PHP_EOL;
                    if($questionSets->count() > 0) {
                        foreach($questionSets as $questionSet) {
                            if($msgId == 'CF_HSP_10') {
                                $question->obs_key = 'HSP';
                                $question->save();
                                echo "updated CF_HSP_10 obs_key to HSP".PHP_EOL;
                                $questionSet->aid = null;
                                $questionSet->low = null;
                                $questionSet->high = null;
                                $questionSet->action = "fxGoto('CF_HSP_20')";
                            } else if($msgId == 'CF_HSP_20') {
                                $question->obs_key = 'HSP_TYPE';
                                $question->save();
                                echo "updated CF_HSP_10 obs_key to HSP_TYPE".PHP_EOL;
                                $answer = CPRulesAnswers::where('value', '=', 'ER')
                                    ->first();
                                $questionSet->aid = $answer->aid;
                                $questionSet->low = null;
                                $questionSet->high = null;
                                $questionSet->action = "fxGoto('CF_HSP_30')";
                            } else if($msgId == 'CF_HSP_30') {
                                $question->obs_key = 'HSP_DISC';
                                $question->save();
                                echo "updated CF_HSP_10 obs_key to HSP_DISC".PHP_EOL;
                                $questionSet->aid = null;
                                $questionSet->low = null;
                                $questionSet->high = null;
                                $questionSet->action = "fxGoto('CF_HSP_EX_01')";
                            } else {
                                $questionSet->action = "fxGoto('CF_HSP_EX_01')";
                                if ($questionSet->aid == 1) {
                                    $questionSet->aid = 11;
                                }
                                if ($questionSet->aid == 2) {
                                    $questionSet->aid = 12;
                                }
                            }
                            echo "updating question set $questionSet->qsid".PHP_EOL;
                            $questionSet->save();
                        }
                    }

                    echo "start item".$question->qid.PHP_EOL;
                    // update item
                    $item = CPRulesItem::where('qid', '=', $question->qid)
                        ->where('pcp_id', '=', $pcpId)->first();
                    if(empty($item)) {
                        $item = new CPRulesItem;
                    }
                    $item->pcp_id = $pcpId;
                    $item->items_parent = 0;
                    $item->qid = $question->qid;
                    $item->items_text = $itemInfo['items_text'];
                    $item->save();
                    echo "added/updated item " . $item->items_text . "(" . $item->items_id . ")" . PHP_EOL;

                    echo "itemmeta - APP_EN - ".$itemInfo['APP_EN'].PHP_EOL;
                    // meta - APP_EN
                    if(isset($itemInfo['APP_EN']) && !empty($item)) {
                        $meta = CPRulesItemMeta::where('items_id', '=', $item->items_id)
                            ->where('meta_key', '=', 'APP_EN')->first();
                        if(empty($meta)) {
                            $meta = new CPRulesItemMeta;
                        }
                        $meta->items_id = $item->items_id;
                        $meta->meta_key = 'APP_EN';
                        $meta->meta_value = $itemInfo['APP_EN'];
                        $meta->save();
                    }

                    echo "itemmeta - APP_ES - ".$itemInfo['APP_ES'].PHP_EOL;
                    // meta - APP_ES
                    if(isset($itemInfo['APP_ES']) && !empty($item)) {
                        $meta = CPRulesItemMeta::where('items_id', '=', $item->items_id)
                            ->where('meta_key', '=', 'APP_ES')->first();
                        if(empty($meta)) {
                            $meta = new CPRulesItemMeta;
                        }
                        $meta->items_id = $item->items_id;
                        $meta->meta_key = 'APP_ES';
                        $meta->meta_value = $itemInfo['APP_ES'];
                        $meta->save();
                    }
                }
            }
            //die("done with $programId");
        }

        die('end');
    }

}



/*
                     *
                     *
                     *
                    // meta - ui_default
                    if(isset($itemInfo['ui_default'])) {
                        $meta = new CPRulesItemMeta;
                        $meta->items_id = $item->items_id;
                        $meta->meta_key = 'ui_default';
                        $meta->meta_value = $itemInfo['ui_default'];
                        $meta->save();
                    }

                    // meta - ui_sort
                    if(isset($itemInfo['ui_sort'])) {
                        $meta = new CPRulesItemMeta;
                        $meta->items_id = $item->items_id;
                        $meta->meta_key = 'ui_sort';
                        $meta->meta_value = $itemInfo['ui_sort'];
                        $meta->save();
                    }

                    // meta - ui_fld_type
                    if(isset($itemInfo['ui_fld_type'])) {
                        $meta = new CPRulesItemMeta;
                        $meta->items_id = $item->items_id;
                        $meta->meta_key = 'ui_fld_type';
                        $meta->meta_value = $itemInfo['ui_fld_type'];
                        $meta->save();
                    }

                    // child item
                    if(isset($itemInfo['child1'])) {

                        $childItem1 = new CPRulesItem;
                        $childItem1->pcp_id = $pcpId;
                        $childItem1->items_parent = $item->items_id;
                        $childItem1->qid = 0;
                        $childItem1->items_text = $itemInfo['child1']['items_text'];
                        $childItem1->save();
                        echo "added child item ".$childItem1->items_text."(".$childItem1->items_id.")".PHP_EOL;

                        $childItem1Meta = new CPRulesItemMeta;
                        $childItem1Meta->items_id = $childItem1->items_id;
                        $childItem1Meta->meta_key = 'ui_default';
                        $childItem1Meta->meta_value = $itemInfo['child1']['ui_default'];
                        $childItem1Meta->save();
                        $childItem1Meta = new CPRulesItemMeta;
                        $childItem1Meta->items_id = $childItem1->items_id;
                        $childItem1Meta->meta_key = 'ui_sort';
                        $childItem1Meta->meta_value = $itemInfo['child1']['ui_sort'];
                        $childItem1Meta->save();
                        $childItem1Meta = new CPRulesItemMeta;
                        $childItem1Meta->items_id = $childItem1->items_id;
                        $childItem1Meta->meta_key = 'ui_show_detail';
                        $childItem1Meta->meta_value = $itemInfo['child1']['ui_show_detail'];
                        $childItem1Meta->save();
                        $childItem1Meta = new CPRulesItemMeta;
                        $childItem1Meta->items_id = $childItem1->items_id;
                        $childItem1Meta->meta_key = 'radio_options';
                        $childItem1Meta->meta_value = $itemInfo['child1']['radio_options'];
                        $childItem1Meta->save();
                        $childItem1Meta = new CPRulesItemMeta;
                        $childItem1Meta->items_id = $childItem1->items_id;
                        $childItem1Meta->meta_key = 'ui_fld_select';
                        $childItem1Meta->meta_value = $itemInfo['child1']['ui_fld_select'];
                        $childItem1Meta->save();
                        $childItem1Meta = new CPRulesItemMeta;
                        $childItem1Meta->items_id = $childItem1->items_id;
                        $childItem1Meta->meta_key = 'ui_fld_type';
                        $childItem1Meta->meta_value = $itemInfo['child1']['ui_fld_type'];
                        $childItem1Meta->save();
                    }
                    */
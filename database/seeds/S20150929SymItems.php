<?php

use App\CPRulesPCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\CPRulesQuestions;
use App\Observation;
use App\ObservationMeta;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20150929SymItems extends Seeder {

    public function run()
    {

        $programIds = array(7, 8, 9, 10, 11);

        $newItems = array(
            'CF_SYM_51' => array(
                'items_text' => 'Shortness of breath',
                'ui_default' => 'Inactive',
                'ui_sort' => '1',
                'ui_fld_type' => 'SELECT',
            ),
            'CF_SYM_52' => array(
                'items_text' => 'Coughing/wheezing',
                'ui_default' => 'Inactive',
                'ui_sort' => '2',
                'ui_fld_type' => 'SELECT',
            ),
            'CF_SYM_53' => array(
                'items_text' => 'Chest pain/tightness',
                'ui_default' => 'Inactive',
                'ui_sort' => '3',
                'ui_fld_type' => 'SELECT',
            ),
            'CF_SYM_54' => array(
                'items_text' => 'Fatigue',
                'ui_default' => 'Inactive',
                'ui_sort' => '4',
                'ui_fld_type' => 'SELECT',
            ),
            'CF_SYM_55' => array(
                'items_text' => 'Weakness/dizziness',
                'ui_default' => 'Inactive',
                'ui_sort' => '5',
                'ui_fld_type' => 'SELECT',
            ),
            'CF_SYM_56' => array(
                'items_text' => 'Swelling in legs/feet',
                'ui_default' => 'Inactive',
                'ui_sort' => '6',
                'ui_fld_type' => 'SELECT',
            ),
            'CF_SYM_57' => array(
                'items_text' => 'Feeling down/sleep changes',
                'ui_default' => 'Inactive',
                'ui_sort' => '7',
                'ui_fld_type' => 'SELECT',
            ),
        );

        foreach($programIds as $programId) {
            echo PHP_EOL."Program $programId".PHP_EOL.PHP_EOL;
            // first remove all existing items
            CPRulesItem::whereHas('pcp', function ($q) use ($programId) {
                    $q->where('section_text', '=', 'Symptoms to Monitor');
                    $q->where('prov_id', '=', $programId);
                })
                ->delete();
            echo "removed all items for $programId".PHP_EOL;
            // get pcp section for program
            $pcp = CPRulesPCP::where('section_text', '=', 'Symptoms to Monitor')
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
                    // create item
                    $item = new CPRulesItem;
                    $item->pcp_id = $pcpId;
                    $item->items_parent = 0;
                    $item->qid = $question->qid;
                    $item->items_text = $itemInfo['items_text'];
                    $item->save();
                    echo "added item ".$item->items_text."(".$item->items_id.")".PHP_EOL;

                    // meta - ui_default
                    $meta = new CPRulesItemMeta;
                    $meta->items_id = $item->items_id;
                    $meta->meta_key = 'ui_default';
                    $meta->meta_value = $itemInfo['ui_default'];
                    $meta->save();

                    // meta - ui_sort
                    $meta = new CPRulesItemMeta;
                    $meta->items_id = $item->items_id;
                    $meta->meta_key = 'ui_sort';
                    $meta->meta_value = $itemInfo['ui_sort'];
                    $meta->save();

                    // meta - ui_fld_type
                    $meta = new CPRulesItemMeta;
                    $meta->items_id = $item->items_id;
                    $meta->meta_key = 'ui_fld_type';
                    $meta->meta_value = $itemInfo['ui_fld_type'];
                    $meta->save();
                }
            }
        }

        die('end');
    }

}
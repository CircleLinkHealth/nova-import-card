<?php namespace database\seeds;

use App\Program;
use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CarePlanItem;
use App\CPRulesUCP;
use App\CPRulesQuestions;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151215CarePlanMigration1 extends Seeder {


    public function run()
    {

        $programs = Program::where('blog_id', '>', '6')->get();
        if(empty($programs)) {
            dd('no programs');
        }
        foreach($programs as $program) {
            // get all items
            $items = CPRulesItem::whereHas('pcp', function ($q) use ($program) {
                $q->where('prov_id', '=', $program->blog_id);
            })->get();
            if (count($items) > 0) {
                // counts
                $i = 0;
                $tod = 0;
                $msk = 0;

                // duplicates
                $duplicates = array(); // track counts on duplicates, key => count

                $itemNames = array();
                foreach ($items as $item) {

                    $itemNames[$i] = array(
                        'items_id' => $item->items_id,
                        'items_text' => $item->items_text,
                        'name' => '',
                    );

                    $nameFormatted = $item->items_text;
                    $nameFormatted = preg_replace("/[^A-Za-z0-9 ]/", '', $nameFormatted);

                    //TOD
                    if (strlen($item->items_text) > 30) {
                        // catch TOD
                        $nameFormatted = 'tod-' . $nameFormatted;//.$tod;
                        $tod++;
                    }

                    // MISC
                    if (strlen($item->items_text) == 0) {
                        $nameFormatted = 'misc-' . $nameFormatted;//.$msk;
                        $msk++;
                    }

                    // QID
                    if ($item->qid != 0) {
                        if ($item->question) {
                            $nameFormatted = $item->question->msg_id . '-' . $nameFormatted;
                            $nameFormatted = str_replace('_', '-', $nameFormatted);
                        } else {
                            // qid doesnt exist, invalid, skip it or remove it
                            $nameFormatted = 'REMOVE';
                        }
                    }

                    $nameFormatted = str_replace('#', '', $nameFormatted);
                    $nameFormatted = strtolower($nameFormatted);
                    $nameFormatted = str_replace(' ', '-', $nameFormatted);
                    $nameFormatted = str_replace('/', '-', $nameFormatted);
                    $nameFormatted = $nameFormatted;

                    // PARENT
                    if ($item->items_parent != 0 && $nameFormatted != 'REMOVE') {
                        // has parent, use it in the name
                        $itemsParent = CPRulesItem::where('items_id', '=', $item->items_parent)->first();
                        if ($itemsParent) {
                            $parentName = strtolower($itemsParent['items_text']);
                            $parentName = preg_replace("/[^A-Za-z0-9 ]/", '', $parentName);
                            $parentName = str_replace(' ', '-', $parentName);
                            $parentName = str_replace('/', '-', $parentName);
                            $nameFormatted = $parentName . '-' . $nameFormatted;
                        } else {
                            // parent doesnt exist, invalid, skip it or remove it
                            $nameFormatted = 'REMOVE';
                        }
                    }

                    // SMS
                    $metaAPPEN = CPRulesItemMeta::where('items_id', '=', $item->items_id)->where('meta_key', '=', 'SMS_EN')->first();
                    if (!empty($metaAPPEN)) {
                        $metaValue = preg_replace("/[^A-Za-z0-9 ]/", '', $metaAPPEN['meta_value']);
                        $metaValueStart = substr($metaValue, 0, 20);
                        $metaValueEnd = '';//substr($metaValue, -7);
                        $metaValue = $metaValueStart . $metaValueEnd;
                        $metaValue = str_replace(' ', '-', $metaValue);
                        $metaValue = str_replace('/', '-', $metaValue);
                        if (strlen($item->items_text) == 0) {
                            $nameFormatted = $nameFormatted . '-' . $metaValue;
                        }
                    }

                    // LOWERCASE
                    $nameFormatted = strtolower($nameFormatted);

                    // SHORTEN
                    $nameFormatted = substr($nameFormatted, 0, 40);

                    // STRIP TRAILING ORPHANED -
                    $nameFormatted = rtrim($nameFormatted, '-') . '';

                    // DOUBLE DASH
                    $nameFormatted = str_replace('--', '-', $nameFormatted);

                    // make sure not already in array
                    $dupeFound = false;
                    foreach ($itemNames as $i => $dupeItem) {
                        if (in_array($nameFormatted, $dupeItem)) {
                            $dupeFound = true;
                        }
                    }
                    if ($dupeFound) {
                        // already in!
                        // add +1 to duplicates array
                        if (!isset($duplicates[$nameFormatted])) {
                            $duplicates[$nameFormatted] = 1;
                        } else {
                            $duplicates[$nameFormatted] = $duplicates[$nameFormatted] + 1;
                        }
                        // add random number
                        //$itemNames[$i]['name'] = 'FIX' .'-' . $nameFormatted . '-' . rand(10001, 50000);
                        $itemNames[$i]['name'] = $nameFormatted . '-' . ($duplicates[$nameFormatted] + 1);
                        //$itemNames[$i]['name'] = $nameFormatted .'-1';
                    } else {
                        $itemNames[$i]['name'] = $nameFormatted;
                    }
                    $i++;
                }
            }

            $i = 0;
            foreach($itemNames as $i => $item) {
                echo $i . '['. $item['items_id'] . ']'.PHP_EOL . '[OLD::'. $item['items_text'] . ']'.PHP_EOL . '[NEW::'. $item['name'] . ']' . PHP_EOL;

                $rulesItem = CPRulesItem::where('items_id', '=', $item['items_id'])->first();
                if($rulesItem) {
                    // first see if match exists in new care plan items
                    $careItem = CareItem::where('name', '=', $item['name'])->first();
                    if(!$careItem) {
                        // doesnt exist yet, so add
                        $careItem = new CareItem;
                        // if theres a qid, set obs_key
                        if(!empty($rulesItem['qid'])) {
                            $rulesQuestion = CPRulesQuestions::where('qid', '=', $rulesItem['qid'])->first();
                            if($rulesQuestion) {
                                $careItem->obs_key = $rulesQuestion['obs_key'];
                            }
                        }
                        $careItem->qid = $rulesItem['qid'];
                        $careItem->name = $item['name'];
                        $careItem->display_name = $item['items_text'];
                        $careItem->description = '';
                        $careItem->save();
                    }
                    $rulesItem->care_item_id = $careItem['id'];
                    $rulesItem->name = $item['name'];
                    $rulesItem->display_name = $item['items_text'];
                    $rulesItem->description = '';
                    $rulesItem->save();
                }
            }

            // now populate parent ids
            $careItems = CareItem::all();
            if($careItems->count() > 0) {
                echo "Start Parent id migration";
                foreach ($careItems as $careItem) {
                    echo "checking care item ".$careItem->id . '-' . $careItem->name . PHP_EOL.PHP_EOL.PHP_EOL;
                    // see if theres a careItem with this value
                    $rulesItem = CPRulesItem::where('care_item_id', '=', $careItem->id)->first();
                    if ($rulesItem) {
                        echo "found rules item ".PHP_EOL.$rulesItem->items_id . '-' . $rulesItem->name . PHP_EOL;
                        // found it, see if it has a parent
                        if($rulesItem['items_parent'] > 0) {
                            echo "has parent ".$rulesItem['items_parent'] . PHP_EOL;
                            // has parent, so get parent
                            $rulesItemParent = CPRulesItem::where('items_id', '=', $rulesItem['items_parent'])->first();
                            if ($rulesItemParent) {
                                if(!empty($rulesItemParent['care_item_id'])) {
                                    $careItem->parent_id = $rulesItemParent['care_item_id'];
                                    echo 'adding parent_id='.$careItem->parent_id.' to care item '.$careItem->name.PHP_EOL;
                                    $careItem->save();

                                }
                            }
                        }
                    }
                }
            }
        }

    }
}
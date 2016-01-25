<?php namespace database\seeds;

use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CarePlanItem;
use App\CPRulesUCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151215CarePlanMigration2 extends Seeder {


    public function run()
    {
        $r=1;
        // now migrate rules_items to care_plan_care_items pivot table
        $rulesUCPs = CPRulesUCP::all();
        if($rulesUCPs->count() > 0) {
            foreach($rulesUCPs as $rulesUCP) {
                echo 'START'.PHP_EOL.'ucp_id = ' . $rulesUCP['ucp_id'] .PHP_EOL;
                $rulesItem = CPRulesItem::where('items_id', '=', $rulesUCP['items_id'])->first();
                if(empty($rulesItem->care_item_id)) {
                    echo 'no care_item_id on items_id ' . $rulesItem['items_id'] .PHP_EOL;
                } else {
                    echo 'items_id = ' . $rulesItem['items_id'] .PHP_EOL;



                    // CARE PLAN
                    $carePlan = CarePlan::where('user_id', '=', $rulesUCP['user_id'])->first();
                    if(!$carePlan) {
                        $user = User::find($rulesUCP['user_id']);
                        if(!$user) {
                            echo "NO USER??? ok.. should skip! but not gunna....".PHP_EOL;
                            //continue 1;
                            $programId = '';
                        } else {
                            $programId = $user->program_id;
                        }
                        // add careplan
                        $carePlan = new CarePlan;
                        $carePlan->user_id = $rulesUCP['user_id'];
                        $carePlan->program_id = $programId;
                        $carePlan->name = 'patient-'.$rulesUCP['user_id'].'-default';
                        $carePlan->display_name = 'Patient '.$rulesUCP['user_id'].' Care Plan';
                        $carePlan->type = 'Patient Default';
                        $carePlan->save();
                        echo 'Created new care plan ' . $carePlan->id .PHP_EOL;
                    }



                    // CARE SECTION
                    echo 'CARE SECTION'.PHP_EOL;
                    if($rulesUCP->item) {
                        echo 'rulesUCP->item->pcp '.PHP_EOL;
                        if($rulesUCP->item->pcp) {
                            $careSection = CareSection::where('display_name', '=', $rulesUCP->item->pcp->section_text)->first();
                            if(!$careSection) {
                                $careSection = new CareSection;
                                $sectionText = preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($rulesUCP->item->pcp->section_text));
                                $sectionText = str_replace(' ', '-', $sectionText);
                                $sectionText = str_replace('--', '-', $sectionText);
                                $careSection->name = $sectionText;
                                $careSection->display_name = $rulesUCP->item->pcp->section_text;
                                $careSection->save();
                                echo 'Created new care section ' . $careSection->id .PHP_EOL;
                            }
                            // attach if doesnt already exist
                            $carePlanSection = $carePlan->careSections()->where('section_id', '=', $careSection['id'])->first();
                            if(empty($carePlanSection)) {
                                $carePlan->careSections()->attach(array($careSection['id'] => array('status' => 'active')));
                                echo $r.' attached section! Plan '.$carePlan['name'].' - Section ' . $careSection['name'] .PHP_EOL;
                            } else {
                                echo $r.' section already attached! Plan '.$carePlan['name'].' - Section ' . $careSection['name'] .PHP_EOL;
                            }
                        } else {
                            echo "NO PCP?";
                        }
                    } else {
                        echo "NO ITEM?";
                    }



                    // CARE ITEM
                    echo 'CARE ITEM'.PHP_EOL;
                    $careItem = CareItem::where('name', '=', $rulesItem['name'])->first();
                    if($careItem) {
                        // build pivot data
                        echo 'care_item_id = ' . $careItem['id'] .PHP_EOL;
                        if(is_null($rulesUCP['meta_value'])) {
                            $rulesUCP['meta_value'] = '';
                        }
                        $rowData = array('section_id' => $careSection['id'],
                            'meta_key' => $rulesUCP['meta_key'],
                            'meta_value' => $rulesUCP['meta_value']);

                        // get meta for columns
                        echo 'rulesUCP->>meta '.PHP_EOL;
                        if($rulesItem->meta->count() > 0) {
                            // what meta are we allowing in? needs to correspond exactly to care_items column name
                            $allowed = array(
                                'meta_key',
                                'meta_value',
                                'alert_key',
                                'ui_placeholder',
                                'ui_default',
                                'ui_title',
                                'ui_fld_type',
                                'ui_show_detail',
                                'ui_row_start',
                                'ui_row_end',
                                'ui_sort',
                                'ui_col_start',
                                'ui_col_end',
                                'track_as_observation',
                                'APP_EN',
                                'APP_ES');
                            foreach($rulesItem->meta as $meta) {
                                if(in_array($meta['meta_key'], $allowed)) {
                                    $key = $meta['meta_key'];
                                    // transforms
                                    if($key == 'track_as_observation') {
                                        $key = 'ui_track_as_observation';
                                    }
                                    if($key == 'APP_EN') {
                                        $key = 'msg_app_en';
                                    }
                                    if($key == 'APP_ES') {
                                        $key = 'msg_app_es';
                                    }
                                    $rowData[$key] = $meta['meta_value'];
                                }
                            }
                        }
                        // attach if doesnt already exist
                        $carePlanItem = $carePlan->careItems()->where('item_id', '=', $careItem['id'])->first();
                        if(empty($carePlanItem)) {
                            $carePlan->careItems()->attach(array($careItem['id'] => $rowData));
                            echo $r.' attached! Plan '.$carePlan->id.' - Item ' . $careItem['id'] .PHP_EOL;
                        } else {
                            echo $r.' already exists! Plan '.$carePlan->id.' - Item ' . $careItem['id'] .PHP_EOL;
                        }
                        $careItem->save();
                        $r++;
                    }
                }
            }
        }

    }
}
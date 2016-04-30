<?php namespace database\seeds;

use App\WpBlog;
use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\User;
use App\Permission;
use App\Role;
use App\UserMeta;
use App\CarePlanItem;
use App\CPRulesUCP;
use App\CPRulesPCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\Services\CareplanUIService;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151215CarePlanMigration3 extends Seeder {

    public function attachCareItem(CarePlan $carePlan, $rulesItemId, $sectionId, $metaKey, $metaValue) {
        // ATTACH CARE ITEM TO CAREPLAN
        $rulesItem = CPRulesItem::find($rulesItemId);
        if(!$rulesItem) {
            return false;
        }
        echo 'CARE ITEM'.PHP_EOL;
        $careItem = CareItem::where('name', '=', $rulesItem['name'])->first();
        if($careItem) {
            // build pivot data
            echo 'care_item_id = ' . $careItem['id'] .PHP_EOL;
            if(is_null($metaValue)) {
                $rulesUCP['ui_default'] = '';
            }
            $rowData = array('section_id' => $sectionId,
                'meta_key' => $metaKey,
                'meta_value' => $metaValue);

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
                echo 'attached! Plan '.$carePlan->id.' - Item ' . $careItem['id'] .PHP_EOL;
            } else {
                echo 'already exists! Plan '.$carePlan->id.' - Item ' . $careItem['id'] .PHP_EOL;
            }
            $careItem->save();
        }
    }


    // this will create the system default careplan
    public function run() {
        echo "start";
        $programs = WpBlog::where('blog_id', '>', '6')->get();
        if(empty($programs)) {
            dd('no programs');
        }
        foreach($programs as $program) {
            // get pcp sections
            $pcps = CPRulesPCP::where('prov_id', '=', $program->blog_id)->lists('pcp_id')->all();

            // get items for these sections
            $items = CPRulesItem::whereIn('pcp_id', $pcps)->get();
            if (empty($items)) {
                // skip if no items
                continue 1;
            }

            // set careplan
            $carePlan = CarePlan::where('program_id', '=', $program->blog_id)->where('type', '=', 'Program Default')->first();
            if(!$carePlan) {
                // create new careplan if doesnt exist
                $carePlan = new CarePlan;
                $carePlan->name = 'program-' . $program->name . '-default';
                $carePlan->display_name = 'Program ' . $program->display_name . ' Default';
                $carePlan->type = 'Program Default';
                $carePlan->program_id = $program->blog_id;
                $carePlan->save();
            }
            $s = 0;

            $pcpSections = CPRulesPCP::where('prov_id', '=', $program->blog_id)->get();
            if (count($pcpSections) > 0) {
                foreach ($pcpSections as $pcpSection) {
                    // --------------------
                    // PROCESS CARE SECTION
                    // --------------------
                    echo PHP_EOL . $s . ' START SECTION ' . $pcpSection['section_text'] . PHP_EOL;
                    $careSection = CareSection::where('display_name', '=', $pcpSection['section_text'])->first();
                    if (!$careSection) {
                        echo 'No careSection found, skipping ' . $pcpSection['section_text'] . PHP_EOL;
                        $s++;
                        continue 1;
                    }
                    $pcpSectionData = (new CareplanUIService)->getCareplanSectionData($program->blog_id, $pcpSection->section_text, false);
                    if (!$pcpSectionData) {
                        echo 'No sectionData found, skipping ' . $pcpSection['section_text'] . PHP_EOL;
                        $s++;
                        continue 1;
                    }
                    // attach if doesnt already exist
                    $carePlanSection = $carePlan->careSections()->where('section_id', '=', $pcpSection['id'])->first();
                    if (empty($carePlanSection)) {
                        $carePlan->careSections()->attach(array($careSection['id'] => array('status' => 'active')));
                        echo 'attached section! Plan ' . $carePlan['name'] . ' - Section ' . $pcpSection['section_text'] . PHP_EOL;
                    } else {
                        echo 'section already attached! Plan ' . $carePlan['name'] . ' - Section ' . $pcpSection['section_text'] . PHP_EOL;
                    }
                    $s++;

                    // --------------------------
                    // PROCESS CARE SECTION ITEMS
                    // --------------------------
                    $parentItems = $pcpSectionData['items'];
                    $itemData = $pcpSectionData['sub_meta'];
                    $p = 0;
                    foreach ($parentItems as $parentItemName => $parentItemInfo) {
                        //echo 'Parent p'.$p.' - ' . $parentItemName . ''.PHP_EOL;
                        foreach ($parentItemInfo as $child1Key => $child1Info) {
                            echo $child1Key . PHP_EOL;
                            // does it have children?
                            if (isset($itemData[$parentItemName][$child1Key])) {
                                // HAS CHILDREN ITEMS
                            } else if (isset($itemData[$parentItemName][0][$child1Key]['items_id'])) {
                                // SINGLETON, HAS NO CHILDREN
                            }
                            // ensure status is set
                            if (strlen($child1Info['status']) < 3) {
                                $child1Info['status'] = 'Inactive';
                            }
                            echo "--Adding item " . $itemData[$parentItemName][0][$child1Key]['items_id'] . " meta_key = status meta_value = " . $child1Info['status'] . PHP_EOL;
                            $this->attachCareItem($carePlan, $itemData[$parentItemName][0][$child1Key]['items_id'], $careSection['id'], 'status', $child1Info['status']);
                            if (isset($itemData[$parentItemName][$child1Key])) {
                                foreach ($itemData[$parentItemName][$child1Key] as $child2Key => $child2Info) {
                                    // set null to empty string
                                    if (strtolower($child2Info['ui_default']) == 'null') {
                                        $child2Info['ui_default'] = '';
                                    }
                                    // add to ucp
                                    echo "----Adding item " . $child2Info['items_id'] . " meta_key = value meta_value = " . $child2Info['ui_default'] . PHP_EOL;
                                    $this->attachCareItem($carePlan, $child2Info['items_id'], $careSection['id'], 'value', $child2Info['ui_default']);
                                }
                            }
                        }
                    }
                }
            }

            // get all program users and attach them to this programs careplan
            $programUsers = User::where('program_id', '=', $program->blog_id)->get();
            if($programUsers->count() > 0) {
                foreach($programUsers as $user) {
                    $user->care_plan_id = $carePlan->id;
                    $user->save();
                }
            }
        }
    }

}
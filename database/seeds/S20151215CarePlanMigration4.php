<?php namespace database\seeds;

use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CareItemCarePlan;
use App\CPRulesUCP;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20151215CarePlanMigration4 extends Seeder {


    public function run()
    {

        // now populate parent ids
        $c = 1;
        $carePlans = CarePlan::where('id', '>=', 1)->get();
        if($carePlans->count() > 0) {
            echo "Start Care Plan Loop".PHP_EOL;
            foreach ($carePlans as $carePlan) {
                $carePlanItems = CareItemCarePlan::where('plan_id', '=', $carePlan->id)->get();
                if($carePlanItems->count() > 0) {
                    echo "Start Care Plan ".$carePlan->id.PHP_EOL;
                    foreach ($carePlanItems as $carePlanItem) {
                        echo PHP_EOL.PHP_EOL."Start Care Plan Item ".$carePlanItem->id.PHP_EOL;

                        // skip if no care item relation
                        if (is_null($carePlanItem->careItem)) {
                            echo "no care_item, skipping".PHP_EOL;
                            continue 1;
                        }

                        // skip if care_item.parent_id = 0
                        if($carePlanItem->careItem->parent_id == 0) {
                            echo "care_item.parent_id = 0, skipping".PHP_EOL;
                            continue 1;
                        }

                        // get parent care item
                        echo "found care item ". $carePlanItem->careItem->id ." - ". $carePlanItem->careItem->name ." with parent_id = ".$carePlanItem->careItem->parent_id.PHP_EOL;
                        echo "lookup parent care_item".PHP_EOL;
                        $careItemParent = CareItem::where('id', '=', $carePlanItem->careItem->parent_id)->first();

                        // skip if no care item relation
                        if (empty($careItemParent)) {
                            echo "no parent care_item, skipping".PHP_EOL;
                            continue 1;
                        }

                        // get id for parent care plan item
                        echo "found parent care_item ". $careItemParent->id ." - ". $careItemParent->name.PHP_EOL;
                        echo "lookup parent care_item_care_plan id".PHP_EOL;
                        $carePlanItemParent = CareItemCarePlan::where('item_id', '=', $careItemParent->id)
                            ->where('plan_id', '=', $carePlan->id)
                            ->first();

                        // skip if no care item relation
                        if (empty($carePlanItemParent)) {
                            echo "no parent care_item_care_plan, skipping".PHP_EOL;
                            continue 1;
                        }

                        // update
                        echo "found parent care_item_care_plan ". $carePlanItemParent->id. " - plan ".$carePlanItemParent->plan_id.PHP_EOL;
                        $carePlanItem->parent_id = $carePlanItemParent->id;
                        $carePlanItem->save();
                        echo "updated - SUCCESS!!".PHP_EOL;

                        //$carePlanItemParent->parent_
                        if($c == 5) {
                            //die('HI 5');
                        }
                        $c++;
                    }
                }
            }
        }

    }
}
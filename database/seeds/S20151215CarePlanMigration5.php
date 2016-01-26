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

class S20151215CarePlanMigration5 extends Seeder {


    public function run()
    {

        // cleanup

        // remove any items with 'remove' in the name
        $careItems = CareItem::where('name', 'like', '%remove%')->get();
        if($careItems->count() > 0) {
            echo "Start Remove Care Items Loop".PHP_EOL;
            foreach ($careItems as $careItem) {
                echo PHP_EOL.PHP_EOL.PHP_EOL." Remove Care Item ". $careItem->id ." name = ". $careItem->name .PHP_EOL;
                $carePlanItems = CarePlanItem::where('item_id', '=', $careItem->id)->get();
                if($carePlanItems->count() > 0) {
                    foreach ($carePlanItems as $carePlanItem) {
                        echo "Remove Care Plan Item ".$carePlanItem->id.PHP_EOL;
                        $carePlanItem->delete();
                    }
                }
                echo PHP_EOL."Remove Care Item ".$careItem->id.PHP_EOL;
                $careItem->userValues()->delete();
                $careItem->delete();
            }
        }

        /*
        // remove any parent item w/o a title
        $carePlanItems = CarePlanItem::where('parent_id', '=', '0')
            ->where('meta_key', '=', 'value')
            ->get();
        if($carePlanItems->count() > 0) {
            echo "Start Remove Care Items Loop".PHP_EOL;
            foreach ($carePlanItems as $carePlanItem) {
                echo PHP_EOL.PHP_EOL.PHP_EOL." Remove Care Item ". $carePlanItem->id ." name = ".PHP_EOL;
                $carePlanItem->delete();
            }
        }
        */
    }
}
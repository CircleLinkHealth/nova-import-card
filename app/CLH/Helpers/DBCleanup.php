<?php namespace App\CLH\Helpers;

use App\CPRulesItem;
use App\CPRulesPCP;

class DBCleanup
{

    public static function nukeItemsAndTheirMeta($itemText)
    {
        $rulesItem = CPRulesItem::whereItemsText($itemText)->get();

        foreach ($rulesItem as $item) {
            $item->delete();
        }
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Helpers;

use App\CPRulesItem;

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

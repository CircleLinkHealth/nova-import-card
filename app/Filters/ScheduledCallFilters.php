<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/16/2018
 * Time: 11:18 PM
 */

namespace App\Filters;


class ScheduledCallFilters extends CallFilters
{
    public function globalFilters(): array
    {
        return [
            'scheduled' => ""
        ];
    }
}
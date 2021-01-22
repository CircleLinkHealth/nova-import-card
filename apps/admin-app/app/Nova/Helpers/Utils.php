<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Helpers;

class Utils
{
    public static function getCssToHideEditButton($row)
    {
        return "<style>
a[dusk='{$row->id}-edit-button'], a[dusk='edit-resource-button'] {
    display: none;
}
</style>";
    }
}

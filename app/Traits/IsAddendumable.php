<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 09/26/2017
 * Time: 10:53 PM
 */

namespace App\Traits;

use App\Models\Addendum;

trait IsAddendumable
{
    public function addendums()
    {
        return $this->morphMany(Addendum::class, 'addendumable');
    }
}

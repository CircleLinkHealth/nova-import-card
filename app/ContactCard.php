<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactCard extends Model
{
    /**
     * Get all of the owning contactCardable models.
     */
    public function contactCardable()
    {
        return $this->morphTo();
    }
}

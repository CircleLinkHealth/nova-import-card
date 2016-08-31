<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NurseContactWindow extends Model
{

    protected $table = 'nurse_contact_window';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    // START RELATIONSHIPS

    public function nurse()
    {
        return $this->belongsTo(NurseInfo::class);
    }

    // END RELATIONSHIPS

}

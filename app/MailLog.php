<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{

    /**
     * @return array
     */
    public function mailable()
    {
        return $this->morphTo();
    }

}

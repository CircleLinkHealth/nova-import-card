<?php namespace App;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{

    const CCM_TIME_ROLES = [
        'care-center',
        'med_assistant',
        'provider'
    ];



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_roles';
}

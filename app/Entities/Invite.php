<?php

namespace App\Entities;

use App\Role;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Invite extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'inviter_id',
        'role_id',
        'email',
        'subject',
        'message',
        'code',
    ];


    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id', 'ID');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}

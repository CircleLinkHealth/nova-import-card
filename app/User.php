<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function patientInfo()
    {
        return $this->hasOne(Patient::class, 'id');
    }

    public function phoneNumber()
    {
        return $this->hasOne(PhoneNumber::class);
    }

    public function url()
    {
        return $this->hasOne(InvitationLink::class);
    }

    public function surveys()
    {

        return $this->belongsToMany(Survey::class, 'users_surveys', 'user_id', 'survey_id')
                    ->withPivot([
                        'survey_instance_id',
                        'status',
                    ]);
    }
}

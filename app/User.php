<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends \CircleLinkHealth\Customer\Entities\User
{
    use \Laravel\Nova\Actions\Actionable;
}

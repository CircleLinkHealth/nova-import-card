<?php

namespace App;

use Laravel\Nova\Actions\Actionable;

class User extends \CircleLinkHealth\Customer\Entities\User
{
    use Actionable;
}

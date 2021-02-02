<?php

namespace App;

use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;

class User extends \CircleLinkHealth\Customer\Entities\User
{
    use SelfEnrollableTrait;
}

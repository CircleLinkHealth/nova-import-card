<?php

namespace CircleLinkHealth\SelfEnrollment\Entities;

use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;

class User extends \CircleLinkHealth\Customer\Entities\User
{
    use SelfEnrollableTrait;
}

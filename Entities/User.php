<?php

namespace CircleLinkHealth\SelfEnrollment\Entities;

use CircleLinkHealth\SelfEnrollment\Traits\SelfEnrollableTrait;

class User extends \CircleLinkHealth\Customer\Entities\User
{
    use SelfEnrollableTrait;
}

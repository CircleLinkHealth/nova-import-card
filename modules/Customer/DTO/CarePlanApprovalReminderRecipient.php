<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\DTO;

use CircleLinkHealth\Customer\Entities\User;

class CarePlanApprovalReminderRecipient
{
    private string $email;
    private string $name;
    private string $role;

    public function __construct(string $name, string $email, string $role)
    {
        $this->name  = $name;
        $this->email = $email;
        $this->role  = $role;
    }

    public function email(): string
    {
        return $this->email;
    }

    public static function fromUser(User $recipient)
    {
        return new static($recipient->getFullName(), $recipient->email, $recipient->practiceOrGlobalRole()->name);
    }

    public function isProvider(): bool
    {
        return 'provider' === $this->role;
    }

    public function name(): string
    {
        return $this->name;
    }
}

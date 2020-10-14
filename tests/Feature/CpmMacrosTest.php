<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\CustomerTestCase;

class CpmMacrosTest extends CustomerTestCase
{
    public function test_forget_using_model_key_macro()
    {
        $users = User::take(5)->get();

        self::assertTrue(is_a($users, EloquentCollection::class));

        $count = $users->count();

        $users->forgetUsingModelKey('id', $firstId = $users->first()->id);

        self::assertTrue($users->count() === $count - 1);
        self::assertFalse($users->contains('id', $firstId));
    }
}

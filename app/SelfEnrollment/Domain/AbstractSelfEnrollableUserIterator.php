<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractSelfEnrollableUserIterator
{
    /**
     * The chunked DB rows we ran an action on so far.
     *
     * @var int
     */
    private $dispatched = 0;

    /**
     * Run an action on a User.
     */
    abstract public function action(User $user): void;

    /**
     * The query to get Users.
     */
    abstract public function query(): Builder;

    /**
     * Run an action on Users chunked from the DB.
     */
    public function run(): void
    {
        $this->query()->chunk($this->chunkSize(), function ($users) {
            $users->each(function (User $user) {
                $this->action($user);

                if ( ! is_null($this->limit()) && ++$this->dispatched === $this->limit()) {
                    return false;
                }
            });
        });
    }

    protected function chunkSize(): int
    {
        return 100;
    }
    
    /**
     * If not null, stop chunking once the number of processed records reaches the limit.
     *
     * @var int|null
     */
    protected function limit(): ?int
    {
        return null;
    }
}

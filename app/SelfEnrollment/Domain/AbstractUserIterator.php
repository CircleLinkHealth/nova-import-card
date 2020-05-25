<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractUserIterator
{
    /**
     * @var int|null
     */
    protected $practiceId;
    /**
     * @var Carbon
     */
    protected $twoDaysAgo;
    /**
     * @var Carbon
     */
    protected $untilEndOfDay;

    public function __construct(Carbon $endDate, Carbon $startDate, ?int $practiceId = null)
    {
        $this->untilEndOfDay = $endDate;
        $this->twoDaysAgo    = $startDate;
        $this->practiceId    = $practiceId;
    }

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
            });
        });
    }

    protected function chunkSize()
    {
        return 100;
    }
}

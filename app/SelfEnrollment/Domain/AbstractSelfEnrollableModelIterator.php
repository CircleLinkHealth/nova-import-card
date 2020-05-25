<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\SelfEnrollment\Contracts\SelfEnrollable;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractSelfEnrollableModelIterator
{
    /**
     * @var int|null
     */
    protected $practiceId;
    /**
     * @var Carbon
     */
    protected $start;
    /**
     * @var Carbon
     */
    protected $end;

    public function __construct(Carbon $endDate, Carbon $startDate, ?int $practiceId = null)
    {
        $this->end = $endDate;
        $this->start    = $startDate;
        $this->practiceId    = $practiceId;
    }

    /**
     * Run an action on a User.
     */
    abstract public function action(SelfEnrollable $enrollableModel): void;

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

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

abstract class SelfEnrollmentAction
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

    public function __construct(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        $this->untilEndOfDay = $untilEndOfDay;
        $this->twoDaysAgo    = $twoDaysAgo;
        $this->practiceId    = $practiceId;
    }

    abstract protected function action(User $user):void;

    protected function chunkSize()
    {
        return 100;
    }

    public function run()
    {
        $this->query()->chunk($this->chunkSize(), function ($users) {
            $users->each(function (User $user) {
                $this->action($user);
            });
        });
    }

    abstract protected function query(): Builder;
}

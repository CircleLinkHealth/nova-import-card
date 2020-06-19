<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class AbstractSelfEnrollableUserIterator implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The chunked DB rows we ran an action on so far.
     *
     * @var int
     */
    public $dispatched = 0;
    /**
     * The limit on how many invites to send. Set to null for unlimited.
     *
     * @var null
     */
    public $limit;

    /**
     * Run an action on a User.
     */
    abstract public function action(User $patient): void;

    /**
     * Run an action on Users chunked from the DB.
     */
    public function handle()
    {
        $this->query()->chunk($this->chunkSize(), function ($users) {
            $users->each(function (User $user) {
                $this->action($user);

                if ( ! is_null($this->limit()) && ++$this->dispatched >= $this->limit()) {
                    return false;
                }
            });
        });
    }

    /**
     * The query to get Users.
     */
    abstract public function query(): Builder;

    /**
     * @return AbstractSelfEnrollableUserIterator
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'SelfEnrollmentAction',
            get_class($this),
        ];
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
        return $this->limit;
    }
}

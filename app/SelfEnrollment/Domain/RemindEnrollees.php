<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use App\SelfEnrollment\Jobs\SendReminder;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class RemindEnrollees extends AbstractSelfEnrollableUserIterator
{
    /**
     * @var Carbon
     */
    protected $dateInviteSent;
    /**
     * @var int|null
     */
    protected $practiceId;

    /**
     * UnreachablesFinalAction constructor.
     */
    public function __construct(Carbon $dateInviteSent, ?int $practiceId = null, ?int $limit = null)
    {
        $this->practiceId     = $practiceId;
        $this->dateInviteSent = $dateInviteSent;
        $this->limit          = $limit;
    }

    public function action(User $patient): void
    {
        SendReminder::dispatch($patient);
    }

    public function query(): Builder
    {
        return User::where(function ($q) {
            $q->where(function ($q) {
                $q->haveEnrollableInvitationDontHaveReminder($this->dateInviteSent);
            })->orWhere(function ($q) {
                $q->hasSelfEnrollmentInvite($this->dateInviteSent)
                    ->hasSelfEnrollmentInviteReminder($this->dateInviteSent->copy()->addDays(2))
                    ->hasSelfEnrollmentInviteReminder($this->dateInviteSent->copy()->addDays(4), false);
            });
        })
            ->ofType('survey-only')
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee
                    ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
                    ->whereNull('source'); //Eliminates unreachable patients, and only fetches enrollees who have not yet enrolled.
            })->doesntHave('enrollableInfoRequest')->orderBy('created_at', 'asc')
            ->with([
                'enrollee.enrollmentInvitationLinks' => function ($q) {
                    $q
                        ->where('created_at', '>=', now()->subDays(15)->startOfDay())
                        ->orderByDesc('id');
                },
            ])
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}

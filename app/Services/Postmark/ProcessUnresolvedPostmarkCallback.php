<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\UnresolvedPostmarkCallback;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ProcessUnresolvedPostmarkCallback
{
    private bool $isMultiMatch;
    private bool $isUniqueMatch;
    private $matchedUsersFromDatabase;
    private int $recordId;

    /**
     * ManageUnresolvedPostmarkCallback constructor.
     *
     * @param $matchedUsersDataFromDb
     */
    public function __construct(array $matchedUsersDataFromDb, int $recordId)
    {
        $this->matchedUsersFromDatabase = $matchedUsersDataFromDb;
        $this->recordId                 = $recordId;
    }

    /**
     * @return \Collection|\Illuminate\Support\Collection
     */
    public function getSuggestedPatientUserIds()
    {
        $suggestions = collect();

        if ($this->matchedWithMultipleUsers()) {
            $suggestions->push(...$this->matchedUsersFromDatabase['matchUsersResult']->pluck('id'));
        }

        if ($this->matchedWithUniqueUser()) {
            $suggestions->push($this->matchedUsersFromDatabase['matchUsersResult']->id);
        }

        return $suggestions;
    }

    /**
     * @return |null
     */
    public function getUserIdIfMatched()
    {
        if ($this->matchedWithUniqueUser()) {
            return isset($this->matchedUsersFromDatabase['matchUsersResult']->id) ? $this->matchedUsersFromDatabase['matchUsersResult']->id : null;
        }

        return null;
    }

    public function handleUnresolved()
    {
        $suggestedPatientUsersIds = $this->getSuggestedPatientUserIds();

        return $this->saveAsUnresolved($suggestedPatientUsersIds->toArray());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|UnresolvedPostmarkCallback|void
     */
    public function saveAsUnresolved(array $suggestedUsersIds)
    {
        try {
            return UnresolvedPostmarkCallback::firstOrCreate(
                [
                    'postmark_id' => $this->recordId,
                ],
                [
                    'user_id'           => $this->getUserIdIfMatched(),
                    'unresolved_reason' => $this->matchedUsersFromDatabase['reasoning'],
                    'suggestions'       => $suggestedUsersIds,
                ]
            );
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            Log::error("Attempt to save $this->recordId as unresolved has failed. ERROR:$message");
            sendSlackMessage('#carecoach_ops_alerts', "Attempt to save inbound callback request $this->recordId as unresolved has failed.");

            return;
        }
    }

    /**
     * @return bool
     */
    private function matchedWithMultipleUsers()
    {
        return $this->isMultiMatch = $this->matchedUsersFromDatabase['matchUsersResult'] instanceof Collection
            || $this->matchedUsersFromDatabase['matchUsersResult'] instanceof \Illuminate\Support\Collection;
    }

    /**
     * @return bool
     */
    private function matchedWithUniqueUser()
    {
        return $this->matchedUsersFromDatabase['matchUsersResult'] instanceof User;
    }
}

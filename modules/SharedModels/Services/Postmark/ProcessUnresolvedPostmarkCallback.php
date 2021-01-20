<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ProcessUnresolvedPostmarkCallback
{
    private array $matchedUsersFromDatabase;
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
            $suggestions->push(...$this->matchedUsersFromDatabase['matchedData']->pluck('id'));
        }

        if ($this->matchedWithUniqueUser()) {
            $suggestions->push($this->matchedUsersFromDatabase['matchedData']->id);
        }

        return $suggestions;
    }

    /**
     * @return |null
     */
    public function getUserIdIfMatched()
    {
        if ($this->matchedWithUniqueUser()) {
            return $this->matchedUsersFromDatabase['matchedData']->id;
        }

        return null;
    }

    public function handleUnresolved()
    {
        $suggestedPatientUsersIds = $this->getSuggestedPatientUserIds();

        return $this->saveAsUnresolved($suggestedPatientUsersIds->toArray());
    }

    /**
     * @return \CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback|\Illuminate\Database\Eloquent\Model|void
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
        return $this->matchedUsersFromDatabase['matchedData'] instanceof Collection
            || $this->matchedUsersFromDatabase['matchedData'] instanceof \Illuminate\Support\Collection;
    }

    /**
     * @return bool
     */
    private function matchedWithUniqueUser()
    {
        return $this->matchedUsersFromDatabase['matchedData'] instanceof User;
    }
}

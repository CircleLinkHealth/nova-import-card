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
    private $matchedData;
    private int $recordId;

    /**
     * ManageUnresolvedPostmarkCallback constructor.
     * @param $matchedData
     */
    public function __construct($matchedData, int $recordId)
    {
        $this->matchedData = $matchedData;
        $this->recordId    = $recordId;
    }

    /**
     * @return \Collection|\Illuminate\Support\Collection
     */
    public function getSuggestedPatientUserIds()
    {
        $suggestions = collect();

        if ($this->matchedWithMultipleUsers()) {
            $suggestions->push(...$this->matchedData['matchUsersResult']->pluck('id'));
        }

        if ($this->matchedWithUniqueUser()) {
            $suggestions->push($this->matchedData['matchUsersResult']->id);
        }

        return $suggestions;
    }

    public function getUserIdIfMatched()
    {
        if ($this->matchedWithUniqueUser()) {
            return isset($this->matchedData['matchUsersResult']->id) ? $this->matchedData['matchUsersResult']->id : null;
        }

        //todo:        Here Check if suggestions exists. If not then its an error

        return null;
    }

    public function handleUnresolved()
    {
        $suggestedPatientUsersIds = $this->getSuggestedPatientUserIds();

        $this->saveAsUnresolved($suggestedPatientUsersIds->toArray());
    }

    public function saveAsUnresolved(array $suggestedUsersIds)
    {
        $suggestedUsersIds = [
           1,
           2
        ];
        try {
            UnresolvedPostmarkCallback::firstOrCreate(
                [
                    'postmark_id' => $this->recordId,
                ],
                [
                    'user_id'           => $this->getUserIdIfMatched(),
                    'unresolved_reason' => $this->matchedData['reasoning'],
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

    private function matchedWithMultipleUsers()
    {
        return $this->isMultiMatch = $this->matchedData['matchUsersResult'] instanceof Collection;
    }

    private function matchedWithUniqueUser()
    {
        return $this->matchedData['matchUsersResult'] instanceof User;
    }
}

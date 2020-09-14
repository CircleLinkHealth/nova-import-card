<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\UnresolvedPostmarkCallback;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ManageUnresolvedPostmarkCallback
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
//        if ($this->isMultiMatch){
//            return null;
//        }

        if ($this->isUniqueMatch) {
            return isset($this->matchedData['matchUsersResult']->id) ?? $this->matchedData['matchUsersResult']->id;
        }
    }

    public function handleUnresolved()
    {
        $suggestedPatientUsersIds = $this->getSuggestedPatientUserIds();

        $this->saveAsUnresolved($suggestedPatientUsersIds->toArray());
    }

    public function saveAsUnresolved(array $suggestedUsersIds)
    {
        try {
            UnresolvedPostmarkCallback::firstOrCreate(
                [
                    'postmark_id' => $this->recordId,
                ],
                [
                    'user_id'           => $this->getUserIdIfMatched(),
                    'unresolved_reason' => json_encode($this->matchedData['reasoning']),
                    'suggestions'       => json_encode($suggestedUsersIds),
                ]
            );
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            Log::error("Attempt to save $this->recordId as unresolved has failed. ERROR:$message");
            sendSlackMessage('#carecoach_ops_alerts', "Attempt to mark inbound callback request $this->recordId as unresolved has failed.");

            return;
        }
    }

    private function matchedWithMultipleUsers()
    {
        return $this->isMultiMatch = $this->matchedData['matchUsersResult'] instanceof Collection;
    }

    private function matchedWithUniqueUser()
    {
        return $this->isUniqueMatch = $this->matchedData['matchUsersResult'] instanceof User;
    }
}

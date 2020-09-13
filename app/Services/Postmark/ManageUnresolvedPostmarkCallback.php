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
     * @param $matchedData
     * @return \Collection|\Illuminate\Support\Collection
     */
    public function getSuggestedPatientUsers()
    {
        $suggestions = collect();

        if ($this->matchedData instanceof Collection) {
            $suggestions->push($this->matchedData->pluck('id'));
        }

        if ($this->matchedData instanceof User) {
            $suggestions->push($this->matchedData->id);
        }

        return $suggestions;
    }

    public function handleUnresolved()
    {
        $suggestedPatientUsers = $this->getSuggestedPatientUsers();
        $this->saveAsUnresolved($suggestedPatientUsers);
    }

    public function saveAsUnresolved(\Illuminate\Support\Collection $suggestions)
    {
        try {
            UnresolvedPostmarkCallback::firstOrCreate(
                [
                    'postmark_id' => $this->recordId,
                ],
                [
                    'user_id'     => '',
                    'suggestions' => json_encode($suggestions),
                ]
            );
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            Log::error("Attempt to save $this->recordId as unresolved has failed. ERROR:$message");
            sendSlackMessage('#carecoach_ops_alerts', "Attempt to mark inbound callback request $this->recordId as unresolved has failed.");

            return;
        }
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\PostmarkMatchedData;
use CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback;
use Illuminate\Support\Facades\Log;

class ProcessUnresolvedPostmarkCallback
{
    private PostmarkMatchedData $matchedUsersFromDatabase;
    private int $recordId;

    public function __construct(PostmarkMatchedData $matchedUsersDataFromDb, int $recordId)
    {
        $this->matchedUsersFromDatabase = $matchedUsersDataFromDb;
        $this->recordId                 = $recordId;
    }

    public function process(): ?UnresolvedPostmarkCallback
    {
        try {
            return UnresolvedPostmarkCallback::firstOrCreate(
                [
                    'postmark_id' => $this->recordId,
                ],
                [
                    'user_id'           => $this->getUserIdIfPossible(),
                    'unresolved_reason' => $this->matchedUsersFromDatabase->reasoning,
                    'suggestions'       => $this->getSuggestions(),
                ]
            );
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            Log::error("Attempt to save $this->recordId as unresolved has failed. ERROR:$message");
            sendSlackMessage('#carecoach_ops_alerts', "Attempt to save inbound callback request $this->recordId as unresolved has failed.");
        }

        return null;
    }

    private function getSuggestions(): array
    {
        return collect($this->matchedUsersFromDatabase->matched)
            ->map(function (User $user) {
                return [
                    'type' => 0 === $user->id ? 'enrollee' : 'user',
                    'id'   => 0 === $user->id ? $user->enrollee->id : $user->id,
                ];
            })
            ->toArray();
    }

    private function getUserIdIfPossible(): ?int
    {
        if ( ! $this->matchedUsersFromDatabase->isMultiMatch()) {
            $patient = $this->matchedUsersFromDatabase->matched[0];
            if (0 !== $patient->id) {
                return $patient->id;
            }
        }

        return null;
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AttestConditionsForNoteAndAddendum implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ?int $addendumId;

    protected int $noteId;

    protected array $problemIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $problemIds, int $noteId, ?int $addendumId = null)
    {
        $this->problemIds = explode(',', $problemIds);
        $this->noteId     = $noteId;
        $this->addendumId = $addendumId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->problemIds)) {
            Log::error('Attest conditions for note and addendum: No attested problem IDs have been inputted');

            return;
        }

        if ( ! $note = Note::with('call')->find($this->noteId)) {
            Log::error("Attest conditions for note and addendum: Note with ID ($this->noteId) not found");

            return;
        }

        if ( ! $call = $note->call) {
            Log::error("Attest conditions for note and addendum: Call not found for note with (ID) $this->noteId");

            return;
        }

        $call->attachAttestedProblems($this->problemIds, $this->addendumId);
    }
}

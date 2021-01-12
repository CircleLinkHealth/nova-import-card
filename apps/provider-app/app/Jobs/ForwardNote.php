<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Customer\Services\NoteService;
use CircleLinkHealth\SharedModels\Entities\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ForwardNote implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private bool $forceNotify;

    private Note $note;
    private bool $notifyCareTeam;
    private bool $notifyClhSupport;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Note $note, bool $notifyCareTeam, bool $notifyClhSupport, bool $forceNotify)
    {
        $this->note             = $note;
        $this->notifyCareTeam   = $notifyCareTeam;
        $this->notifyClhSupport = $notifyClhSupport;
        $this->forceNotify      = $forceNotify;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NoteService $noteService)
    {
        $noteService->forwardNoteIfYouMust($this->note, $this->notifyCareTeam, $this->notifyClhSupport, $this->forceNotify);
    }
}

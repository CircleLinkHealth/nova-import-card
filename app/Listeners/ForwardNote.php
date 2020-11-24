<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\NoteFinalSaved;
use CircleLinkHealth\Customer\Services\NoteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ForwardNote implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'high';

    /**
     * @var NoteService
     */
    protected $noteService;

    /**
     * Create the event listener.
     */
    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle(NoteFinalSaved $event)
    {
        if ($params = $event->params) {
            if (array_keys_exist(['notifyCareTeam',
                'notifyCLH',
                'forceNotify', ], $params)) {
                $this->noteService->forwardNoteIfYouMust($event->note, $params['notifyCareTeam'], $params['notifyCLH'], $params['forceNotify']);
            }
        }
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace AshAllenDesign\ShortURL\Events;

use AshAllenDesign\ShortURL\Models\ShortURL;
use AshAllenDesign\ShortURL\Models\ShortURLVisit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShortURLVisited
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The short URL that was visited.
     *
     * @var ShortURL
     */
    public $shortURL;

    /**
     * Details of the visitor that visited the short URL.
     *
     * @var ShortURLVisit
     */
    public $shortURLVisit;

    /**
     * Create a new event instance.
     */
    public function __construct(ShortURL $shortURL, ShortURLVisit $shortURLVisit)
    {
        $this->shortURL      = $shortURL;
        $this->shortURLVisit = $shortURLVisit;
    }
}

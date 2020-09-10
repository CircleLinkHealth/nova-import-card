<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CodeGreenCreative\SamlIdp\Events\Assertion;
use LightSaml\ClaimTypes;
use LightSaml\Model\Assertion\Attribute;

class SamlAssertionAttributes
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object $event
     * @return void
     */
    public function handle(Assertion $event)
    {
        $event->attribute_statement
            ->addAttribute(new Attribute(ClaimTypes::PPID, auth()->user()->id))
            ->addAttribute(new Attribute(ClaimTypes::GIVEN_NAME, auth()->user()->display_name));
    }
}

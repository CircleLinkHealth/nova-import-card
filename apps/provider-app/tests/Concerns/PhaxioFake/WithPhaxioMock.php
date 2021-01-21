<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns\PhaxioFake;

use CircleLinkHealth\Core\Contracts\Efax;

trait WithPhaxioMock
{
    /**
     * Mocked Phaxio implementation.
     *
     * @var Efax
     */
    private $phaxio;

    private function phaxio(): Efax
    {
        if ( ! $this->phaxio) {
            $this->phaxio = Phaxio::fake();
        }

        return $this->phaxio;
    }
}

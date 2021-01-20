<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Reports\Sales\Provider\Sections;

use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Reports\Sales\ProviderReportable;
use CircleLinkHealth\CpmAdmin\Reports\Sales\SalesReportSection;
use CircleLinkHealth\CpmAdmin\Reports\Sales\StatsHelper;
use CircleLinkHealth\Customer\Entities\User;

class PracticeDemographics extends SalesReportSection
{
    private $provider;
    private $service;

    public function __construct(
        User $provider,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($provider, $start, $end);
        $this->provider = $provider;
        $this->service  = new StatsHelper(new ProviderReportable($provider));
    }

    public function render()
    {
        if ( ! $this->provider->primaryPractice) {
            \Log::critical("Provider {$this->provider->id} does not have a primary practice set.");

            return 'This provider does not have a primary practice set.';
        }

        return (new \CircleLinkHealth\CpmAdmin\Reports\Sales\Practice\Sections\PracticeDemographics(
            $this->provider->primaryPractice,
            $this->start,
            $this->end
        ))->render();
    }
}
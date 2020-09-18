<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Provider\Sections;

use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\ProviderReportable;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\SalesReportSection;
use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\StatsHelper;
use Carbon\Carbon;
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

        return (new \CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections\PracticeDemographics(
            $this->provider->primaryPractice,
            $this->start,
            $this->end
        ))->render();
    }
}

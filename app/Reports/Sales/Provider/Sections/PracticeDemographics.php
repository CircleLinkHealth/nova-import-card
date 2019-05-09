<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports\Sales\Provider\Sections;

use App\Reports\Sales\ProviderReportable;
use App\Reports\Sales\SalesReportSection;
use App\Reports\Sales\StatsHelper;
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

        return (new \App\Reports\Sales\Practice\Sections\PracticeDemographics(
            $this->provider->primaryPractice,
            $this->start,
            $this->end
        ))->render();
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections;

use CircleLinkHealth\CpmAdmin\Services\Reports\Sales\SalesReportSection;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class PracticeDemographics extends SalesReportSection
{
    private $practice;

    public function __construct(
        Practice $practice,
        Carbon $start,
        Carbon $end
    ) {
        parent::__construct($practice, $start, $end);
        $this->practice = $practice;
    }

    public function render()
    {
        $lead      = optional($this->practice->lead)->getFullName() ?? 'N/A';
        $providers = $this->practice->getCountOfUserTypeAtPractice('provider');
        $mas       = $this->practice->getCountOfUserTypeAtPractice('med_assistant');
        $oa        = $this->practice->getCountOfUserTypeAtPractice('office_admin');

        $disabled_users = User::whereProgramId($this->practice->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'provider')
                    ->orWhere('name', 'med_assistant')
                    ->orWhere('name', 'office_admin');
            })
            ->where('user_status', 0)
            ->get();

        foreach ($disabled_users as $user) {
            if ('provider' == $user->roles[0]->name) {
                --$providers;
            }

            if ('med_assistant' == $user->roles[0]->name) {
                --$mas;
            }

            if ('office_admin' == $user->roles[0]->name) {
                --$oa;
            }
        }

        $total = $providers + $mas + $oa;

        //prevent non negative total
        if ($total < 0) {
            $total = 0;
        }

        return [
            'lead'      => $lead,
            'providers' => $providers,
            'rns'       => $mas,
            'oas'       => $oa,
            'disabled'  => count($disabled_users),

            'total' => $total,
        ];
    }
}

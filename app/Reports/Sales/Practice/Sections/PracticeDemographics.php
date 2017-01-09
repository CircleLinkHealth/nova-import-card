<?php

namespace App\Reports\Sales\Practice\Sections;

use App\Practice;
use App\Reports\Sales\SalesReportSection;
use App\User;
use Carbon\Carbon;

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

    public function renderSection()
    {

        $lead = $this->practice->lead->fullName ?? 'N/A';
        $providers = $this->practice->getCountOfUserTypeAtPractice('provider');
        $mas = $this->practice->getCountOfUserTypeAtPractice('med_assistant');
        $oa = $this->practice->getCountOfUserTypeAtPractice('office_admin');

        $disabled_count = User
            ::whereProgramId($this->practice->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'participant')
                ->where('name', '!=', 'administrator');
            })
            ->whereUserStatus(0)
            ->count();

        $total = $providers + $mas + $oa - $disabled_count;

        //prevent non negative total
        if ($total < 0) {
            $total = 0;
        }

        return [

            'lead'      => $lead,
            'providers' => $providers,
            'rns'       => $mas,
            'oas'       => $oa,
            'disabled'  => $disabled_count,

            'total' => $total,

        ];

    }


}
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
        $cc = $this->practice->getCountOfUserTypeAtPractice('care-center');
        $oa = $this->practice->getCountOfUserTypeAtPractice('office_admin');

        $practice = $this->practice;

        $disabled_count = User
            ::whereHas('practices', function ($q) use ($practice) {
                $q->whereId($practice->id);
            })->whereHas('roles', function ($q){
                $q->where('name', '!=', 'participant');
            })
            ->whereUserStatus(0)
            ->count();

        $total = $providers + $mas + $oa - $disabled_count;

        //prevent non negative total
        if($total < 0){
            $total = 0;
        }

        return [

            'lead' =>  $lead,
            'providers' => $providers,
            'rns' => $mas,
            'oas' => $oa,
            'disabled' => $disabled_count,

            'total' => $total

        ];

    }


}
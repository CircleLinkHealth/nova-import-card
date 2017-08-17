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

    public function render()
    {

        $lead = $this->practice->lead->fullName ?? 'N/A';
        $providers = $this->practice->getCountOfUserTypeAtPractice('provider');
        $mas = $this->practice->getCountOfUserTypeAtPractice('med_assistant');
        $oa = $this->practice->getCountOfUserTypeAtPractice('office_admin');

        $disabled_users = User::whereProgramId($this->practice->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'provider')
                ->orWhere('name', 'med_assistant')
                ->orWhere('name', 'office_admin');
            })
            ->where('user_status', 0)
            ->get();

        foreach ($disabled_users as $user){

            if($user->roles[0]->name == 'provider'){
                $providers--;
            }

            if($user->roles[0]->name == 'med_assistant'){
                $mas--;
            }

            if($user->roles[0]->name == 'office_admin'){
                $oa--;
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
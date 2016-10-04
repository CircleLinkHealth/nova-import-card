<?php

namespace App\Billing;

use App\NurseInfo;
use App\PageTimer;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

class NurseMonthlyBillGenerator
{

    protected $nurse;
    protected $nurseName;
    protected $startDate;
    protected $endDate;

    //Billing Results
    protected $formattedItemizedActivities;
    protected $payable;
    protected $systemTime;

    public function __construct(NurseInfo $newNurse, Carbon $billingDateStart, Carbon $billingDateEnd){

        $this->nurse = $newNurse;
        $this->nurseName = $newNurse->user->fullName;
        $this->startDate = $billingDateStart;
        $this->endDate = $billingDateEnd;

    }

    public function handle(){

        $this->getSystemTimeForNurse();
        
        $this->getItemizedActivities();
        
        $this->formatItemizedActivities();
        
        return $this->generatePdf();

    }

    public function getSystemTimeForNurse(){


        $this->systemTime = PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=' , $this->startDate->toDateString())
                    ->where('updated_at', '<=' , $this->endDate->toDateString());
            })
            ->sum('duration');

        $this->payable = round(($this->systemTime / 3600) * $this->nurse->hourly_rate, 2);

        return '$'.$this->payable;

    }

    public function getItemizedActivities(){

        $activities = PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('updated_at', '>=' , $this->startDate->toDateString())
                    ->where('updated_at', '<=' , $this->endDate->toDateString());
            })
            ->get();

        return $activities;

    }

    public function formatItemizedActivities(){

        $activities = $this->getItemizedActivities();

        $data = [];

        $count = 0;
        foreach ($activities as $activity){

            $data[$count]['Date'] = Carbon::parse($activity->start_time)->toDateString();
            $data[$count]['Start Time'] = Carbon::parse($activity->start_time)->toTimeString();
            $data[$count]['End Time'] = Carbon::parse($activity->end_time)->toTimeString();
            $data[$count]['Patient'] = $activity->patient_id ? $activity->patient_id : 'NA';
            $data[$count]['Duration'] =  $activity->duration . ' seconds';

            $count++;

            $this->formattedItemizedActivities = $data;

        }
        
        $pdf_data = PDF::loadView('billing.nurse.invoice',
            [
                'data' => $data,
                'nurse_billable_time' => $this->systemTime,
                'total_billable_amount' => '$'.$this->payable,
                'nurse_name' => $this->nurse->user->fullName,
                'date_start' => $this->startDate->toDateTimeString(),
                'date_end' => $this->endDate->toDateTimeString(),

            ]);

        return $pdf_data;

    }

    public function generatePdf(){

        $pdf = $this->formatItemizedActivities();

        $name = $this->nurseName . Carbon::now()->toDateTimeString();

        return $pdf->download("{$name}.pdf");

    }

}
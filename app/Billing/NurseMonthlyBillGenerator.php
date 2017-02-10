<?php

namespace App\Billing;

use App\Activity;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Billing\NurseInvoices\VariablePay;
use App\Call;
use App\Nurse;
use App\PageTimer;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

//READ ME
/*
 * This class can be used to generate nurse invoices for a given time range.
 * 
 * Either use handle() or email() for generating vs. sending invoices. 
 */

class NurseMonthlyBillGenerator
{

    //initializations
    protected $nurse;
    protected $nurseName;
    protected $startDate;
    protected $endDate;

    //manual time adds
    protected $hasAddedTime = false;
    protected $addDuration;
    protected $formattedAddDuration;
    protected $addNotes;

    //Billing Results
    protected $formattedItemizedActivities;
    protected $payable;
    protected $percentTime;
    protected $rate;
    protected $total;

    protected $hasReport;

    protected $withVariablePaymentSystem;

    //total time in system
    protected $systemTime;
    protected $formattedSystemTime;

    //total ccm time accumulated
    protected $activityTime;

    public function __construct(Nurse $newNurse,
                                Carbon $billingDateStart,
                                Carbon $billingDateEnd,
                                $withVariablePaymentSystem,
                                $manualTimeAdd = 0,
                                $notes = ''){

        $this->nurse = $newNurse;
        $this->nurseName = $newNurse->user->last_name;
        $this->startDate = $billingDateStart;
        $this->endDate = $billingDateEnd;
        $this->addDuration = $manualTimeAdd;
        $this->addNotes = $notes;
        $this->withVariablePaymentSystem = $withVariablePaymentSystem;

        if($this->addDuration != 0){ $this->hasAddedTime = true; }


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
                $q->where('created_at', '>=', $this->startDate)
                    ->where('created_at', '<=', $this->endDate);
            })
            ->sum('duration');

        //add manual activities.
        $this->systemTime += Activity::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('created_at', '>=', $this->startDate)
                    ->where('created_at', '<=', $this->endDate);
            })
            ->where('logged_from', 'manual_input')
            ->sum('duration');

        $this->activityTime = Activity::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('created_at', '>=', $this->startDate)
                    ->where('created_at', '<=', $this->endDate);
            })
            ->sum('duration');

        //percent calc
        if($this->activityTime == 0 || $this->systemTime == 0){

            $this->percentTime = 0;

        } else {

            $this->percentTime = round(($this->activityTime/$this->systemTime) * 100, 2);

        }

        //format system time, make to 30 mins if time below 1800
        if($this->systemTime != 0 && $this->systemTime != null){

            if( $this->systemTime <= 1800) {

                $this->formattedSystemTime = 0.5;

            } else {

                $this->formattedSystemTime = ceil(($this->systemTime  * 2) / 3600) / 2;

            }

        } else if($this->systemTime == null){

            $this->formattedSystemTime = 0;

        }

        return $this->formattedSystemTime;

    }

    public function getItemizedActivities(){

        $data = [];

        $pageTimers = PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('created_at', '>=', $this->startDate)
                    ->where('created_at', '<=', $this->endDate);
            })
            ->get();


        $offlineActivities = Activity::where('provider_id', $this->nurse->user_id)
            ->where(function ($q){
                $q->where('created_at', '>=', $this->startDate)
                    ->where('created_at', '<=', $this->endDate);
            })
            ->where('logged_from', 'manual_input')
            ->get();


        $pageTimers = $pageTimers->groupBy(function($q) {
            return Carbon::parse($q->created_at)->format('d'); // grouping by days
        });

        $offlineActivities = $offlineActivities->groupBy(function($q) {
            return Carbon::parse($q->created_at)->format('d'); // grouping by days
        });


        foreach ($pageTimers as $activity){

            $data[Carbon::parse($activity[0]['created_at'])->toDateString()] = $activity->sum('duration');

        };

        foreach ($offlineActivities as $offlineActivity){

            if(isset($data[Carbon::parse($offlineActivity[0]['created_at'])->toDateString()])){

                $data[Carbon::parse($offlineActivity[0]['created_at'])->toDateString()] += $offlineActivity->sum('duration');

            } else {

                $data[Carbon::parse($offlineActivity[0]['created_at'])->toDateString()] = $offlineActivity->sum('duration');

            }


        };

        return $data;

    }

    public function formatItemizedActivities(){

        $activities = $this->getItemizedActivities();

        $data = [];

        $this->total['hours'] = $this->formattedSystemTime;
        $this->total['minutes'] = $this->formattedSystemTime * 60;

        $this->payable = $this->formattedSystemTime * $this->nurse->hourly_rate;

        if($this->withVariablePaymentSystem){

            $variable = ( new VariablePay($this->nurse, $this->startDate, $this->endDate))->getItemizedActivities();
            $this->total['after'] = $variable['total']['after'];
            $this->total['towards'] = $variable['total']['towards'];

            $payableVariable = $variable['payable'];
            
            if($payableVariable > $this->payable){

                $this->payable = $payableVariable;
                $this->rate = 'Variable Rates: $30/hr or $10/hr';

            } else {

                $this->rate = 'Fixed Rate: '. $this->nurse->hourly_rate . '/hr';

            }

        } else {

            $variable = false;
            $this->rate = 'Fixed Rate: '. $this->nurse->hourly_rate . '/hr';

        }

        $dayCounterCarbon = Carbon::parse($this->startDate->toDateString());
        $dayCounterDate = $dayCounterCarbon->toDateString();

        //handle any extra time
        if($this->hasAddedTime){

            //round to .5
            $this->formattedAddDuration = ceil(($this->addDuration * 2) / 60) / 2;
            $this->formattedSystemTime += $this->formattedAddDuration;

            $extraMultiplier = ($this->withVariablePaymentSystem) ? 30 : 20;

//            $this->payable += $this->formattedAddDuration * $extraMultiplier;

            $others = [

                'Date'    => $this->addNotes,
                'Minutes' => $this->addDuration,
                'Hours'   => $this->formattedAddDuration

            ];

        } else {

            $others = false;
        }

        while($this->endDate->toDateString() >= $dayCounterDate){

            if(isset($activities[$dayCounterDate])){

                $data[$dayCounterDate] = [

                    'Date'    => $dayCounterDate,
                    'Minutes' => round($activities[$dayCounterDate] / 60, 2),
                    'Hours'   => round($activities[$dayCounterDate] / 3600, 1)

                ];

            } else {

                $data[$dayCounterDate] = [

                    'Date'    => $dayCounterDate,
                    'Minutes' => 0,
                    'Hours'   => 0

                ];

            }

            $dayCounterCarbon->addDay();
            $dayCounterDate = $dayCounterCarbon->toDateString();

        }
        
        $this->formattedItemizedActivities = [
        //days data
            'data' => $data,
            'hasAddedTime' => $this->hasAddedTime,
            'manual_time' => $this->formattedAddDuration,
            'manual_time_notes' => $this->addNotes,
            'manual_time_amount' => $this->formattedAddDuration * $this->nurse->hourly_rate,

        //variable
            'variable_pay' => $variable,
            'total' => $this->total,
            'others' => $others,

            //headers
            'nurse_billable_time' => $this->formattedSystemTime,
            'total_billable_amount' => '$'.$this->payable,
            'total_billable_rate' => $this->rate,
            'nurse_name' => $this->nurse->user->fullName,

            //range
            'date_start' => $this->startDate->format('jS M, Y'),
            'date_end' => $this->endDate->format('jS M, Y')

        ];

        return $this->formattedItemizedActivities;

    }

    public function generatePdf($onlyLink = false){

        $this->formatItemizedActivities();

        $pdf = PDF::loadView('billing.nurse.invoice', $this->formattedItemizedActivities);

        $name = trim($this->nurseName).'-'.Carbon::now()->toDateString();

        $pdf->save( storage_path("download/$name.pdf"), true );

        if($onlyLink){
            return storage_path("download/$name.pdf");
        }

        $data = [
            'name' => $this->nurse->user->fullName,
            'percentage' => $this->percentTime,
            'total_time' => $this->formattedSystemTime,
            'payout' => $this->payable,
        ];

        return [

            'id' => $this->nurse->id,
            'name' => $this->nurseName,
            'email' => $this->nurse->user->email,
            'link' => $name.'.pdf',
            'date_start' => $this->startDate->toDateString(),
            'date_end' => $this->endDate->toDateString(),
            'email_body' => $data
        ];

    }

    public function getCallsPerHourOverPeriod()
    {

        $duration = intval(PageTimer::where('provider_id', $this->nurse->user_id)
            ->where(function ($q) {
                $q->where('created_at', '>=', $this->startDate->toDateString())
                    ->where('created_at', '<=', $this->endDate->toDateString());
            })
            ->sum('billable_duration'));

        $ccm_duration = intval(Activity::where('logger_id', $this->nurse->user_id)
            ->where(function ($q) {
                $q->where('created_at', '>=', $this->startDate->toDateString())
                    ->where('created_at', '<=', $this->endDate->toDateString());
            })
            ->sum('duration'));

        $calls = Call::where('outbound_cpm_id', $this->nurse->user_id)
            ->where(function ($q) {
                $q->where('updated_at', '>=', $this->startDate->toDateString())
                    ->where('updated_at', '<=', $this->endDate->toDateString());
            })
            ->where(function ($k) {
                $k->where('status', '=', 'reached')
                    ->orWhere('status', '=', 'not reached');
            })
            ->count();

        $hours = $duration / 3600;

        if ($calls != 0 && $hours != 0) {
            $percent = round(($ccm_duration / $duration) * 100, 2);
        } else {
            $percent = 0;
        }

        if ($calls == 0 || $hours < 1) {

            return [

                'calls/hour'   => 0,
                'duration'     => round($duration / 3600, 2),
                'ccm_duration' => round($ccm_duration / 3600, 2),
                '%ccm'         => $percent,

            ];

        }

        return [

            'calls/hour'   => round($calls / $hours, 2),
            'duration'     => round($duration / 3600, 2),
            'ccm_duration' => round($ccm_duration / 3600, 2),
            '%ccm'         => $percent,

        ];

    }

}
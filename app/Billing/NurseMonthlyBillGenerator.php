<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Billing;

use App\Billing\NurseInvoices\VariablePay;
use App\Call;
use App\NurseInvoiceExtra;
use App\Services\PdfService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;

//READ ME
/*
 * This class can be used to generate nurse invoices for a given time range.
 *
 * Either use handle() or email() for generating vs. sending invoices.
 */

class NurseMonthlyBillGenerator
{
    protected $activityData;
    //total ccm time accumulated
    protected $activityTime;
    protected $addDuration;
    protected $addNotes;
    protected $bonus;
    protected $endDate;
    protected $formattedAddDuration;

    //Billing Results
    protected $formattedItemizedActivities;
    protected $formattedSystemTime;

    //manual time adds
    protected $hasAddedTime = false;

    protected $hasReport;

    //initializations
    protected $nurse;
    protected $nurseExtras;
    protected $nurseName;
    protected $pageTimerData;
    protected $payable;
    protected $percentTime;
    protected $rate;
    protected $startDate;
    protected $summary;

    //total time in system
    protected $systemTime;
    protected $total;

    protected $withVariablePaymentSystem;

    public function __construct(
        Nurse $newNurse,
        Carbon $billingDateStart,
        Carbon $billingDateEnd,
        $withVariablePaymentSystem,
        $manualTimeAdd = 0,
        $notes = '',
        $summary
    ) {
        $this->nurse                     = $newNurse;
        $this->nurseName                 = $newNurse->user->getFullName();
        $this->startDate                 = $billingDateStart;
        $this->endDate                   = $billingDateEnd;
        $this->addDuration               = $manualTimeAdd;
        $this->addNotes                  = $notes;
        $this->withVariablePaymentSystem = $withVariablePaymentSystem;
        $this->summary                   = $summary;

        $this->pageTimerData = PageTimer::where('provider_id', $this->nurse->user_id)
            ->select(['id', 'duration', 'created_at'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
        $this->activityData = Activity::where('provider_id', $this->nurse->user_id)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $this->nurseExtras = NurseInvoiceExtra::where('user_id', $this->nurse->user_id)->get();

        $addExtraTime = $this->nurseExtras
            ->where('unit', 'minutes')
            ->sum('value');

        $this->bonus = $this->nurseExtras
            ->where('unit', 'usd')
            ->sum('value');

        $this->addDuration = $addExtraTime;

        if (0 != $this->addDuration) {
            $this->hasAddedTime = true;
        }
    }

    public function handle()
    {
        return $this->generatePdf();
    }

    private function formatItemizedActivities()
    {
        $activities = $this->getItemizedActivities();

        $data = [];

        $this->payable = $this->formattedSystemTime * $this->nurse->hourly_rate;

        if ($this->withVariablePaymentSystem) {
            $variable = (new VariablePay(
                $this->nurse,
                $this->startDate,
                $this->endDate,
                $this->summary
            ))->getItemizedActivities();
            $this->total['after']   = $variable['total']['after'];
            $this->total['towards'] = $variable['total']['towards'];

            $payableVariable = $variable['payable'];

            if ($payableVariable > $this->payable) {
                $this->payable = $payableVariable;
                $high_rate     = $this->nurse->high_rate;
                $low_rate      = $this->nurse->low_rate;

                $this->rate = "Variable Rates: ${high_rate}/hr or ${low_rate}/hr";
            } else {
                $this->rate = 'Fixed Rate: '.$this->nurse->hourly_rate.'/hr';
            }
        } else {
            $variable   = false;
            $this->rate = 'Fixed Rate: '.$this->nurse->hourly_rate.'/hr';
        }

        $dayCounterCarbon = Carbon::parse($this->startDate->toDateString());
        $dayCounterDate   = $dayCounterCarbon->toDateString();

        //handle any extra time
        if ($this->hasAddedTime) {
            //round to .5
            $this->formattedAddDuration = ceil(($this->addDuration * 2) / 60) / 2;
            $this->formattedSystemTime += $this->formattedAddDuration;

            $this->payable += ($this->formattedAddDuration * $this->nurse->hourly_rate) + $this->bonus;

            $others = [
                'Date'    => $this->addNotes,
                'Minutes' => $this->addDuration,
                'Hours'   => $this->formattedAddDuration,
            ];
        } else {
            $others = false;
        }

        while ($this->endDate->toDateString() >= $dayCounterDate) {
            if (isset($activities[$dayCounterDate])) {
                $data[$dayCounterDate] = [
                    'Date'    => $dayCounterDate,
                    'Minutes' => round($activities[$dayCounterDate] / 60, 2),
                    'Hours'   => round($activities[$dayCounterDate] / 3600, 1),
                ];
            } else {
                $data[$dayCounterDate] = [
                    'Date'    => $dayCounterDate,
                    'Minutes' => 0,
                    'Hours'   => 0,
                ];
            }

            $dayCounterCarbon->addDay();
            $dayCounterDate = $dayCounterCarbon->toDateString();
        }

        $this->total['hours']   = $this->formattedSystemTime;
        $this->total['minutes'] = $this->formattedSystemTime * 60;

        $this->formattedItemizedActivities = [
            //days data
            'data'               => $data,
            'hasAddedTime'       => $this->hasAddedTime,
            'manual_time'        => $this->formattedAddDuration,
            'manual_time_notes'  => $this->addNotes,
            'manual_time_amount' => $this->formattedAddDuration * $this->nurse->hourly_rate,
            'bonus'              => $this->bonus,

            //variable
            'variable_pay' => $variable,
            'total'        => $this->total,
            'others'       => $others,

            'high_rate' => $this->nurse->high_rate,
            'low_rate'  => $this->nurse->low_rate,

            //headers
            'nurse_billable_time'   => $this->formattedSystemTime,
            'total_billable_amount' => '$'.$this->payable,
            'total_billable_rate'   => $this->rate,
            'nurse_name'            => $this->nurse->user->getFullName(),

            //range
            'date_start' => $this->startDate->format('jS M, Y'),
            'date_end'   => $this->endDate->format('jS M, Y'),
        ];
    }

    private function generatePdf($onlyLink = false)
    {
        $pdfService = app(PdfService::class);

        $this->getSystemTimeForNurse();
        $this->formatItemizedActivities();

        $name     = trim($this->nurseName).'-'.Carbon::now()->toDateString();
        $filePath = storage_path("download/${name}.pdf");

        $pdf = $pdfService->createPdfFromView(
            'billing.nurse.invoice',
            $this->formattedItemizedActivities,
            $filePath
        );

        if ($onlyLink) {
            return $filePath;
        }

        $data = [
            'name'       => $this->nurse->user->getFullName(),
            'percentage' => $this->percentTime,
            'total_time' => $this->formattedSystemTime,
            'payout'     => $this->payable,
        ];

        return [
            'id'         => $this->nurse->id,
            'name'       => $this->nurseName,
            'email'      => $this->nurse->user->email,
            'link'       => $name.'.pdf',
            'date_start' => $this->startDate->toDateString(),
            'date_end'   => $this->endDate->toDateString(),
            'email_body' => $data,
        ];
    }

    private function getCallsPerHourOverPeriod()
    {
        $duration = intval(
            $this->pageTimerData
                ->sum('billable_duration')
        );

        $ccm_duration = intval(
            $this->activityData
                ->sum('duration')
        );

        $calls = Call::where('outbound_cpm_id', $this->nurse->user_id)
            ->where(
                function ($q) {
                    $q->where('updated_at', '>=', $this->startDate->toDateString())
                        ->where('updated_at', '<=', $this->endDate->toDateString());
                }
            )
            ->where(
                function ($k) {
                    $k->where('status', '=', 'reached')
                        ->orWhere('status', '=', 'not reached');
                }
            )
            ->count();

        $hours = $duration / 3600;

        if (0 != $calls && 0 != $hours) {
            $percent = round(($ccm_duration / $duration) * 100, 2);
        } else {
            $percent = 0;
        }

        if (0 == $calls || $hours < 1) {
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

    private function getItemizedActivities()
    {
        $data = [];

        $pageTimers = $this->pageTimerData;

        $offlineActivities = $this->activityData
            ->where('logged_from', 'manual_input');

        $pageTimers = $pageTimers->groupBy(
            function ($q) {
                return Carbon::parse($q->created_at)->format('d'); // grouping by days
            }
        );

        $offlineActivities = $offlineActivities->groupBy(
            function ($q) {
                return Carbon::parse($q->created_at)->format('d'); // grouping by days
            }
        );

        foreach ($pageTimers as $activity) {
            $data[Carbon::parse($activity[0]['created_at'])->toDateString()] = $activity->sum('duration');
        }

        foreach ($offlineActivities as $offlineActivity) {
            if (isset($data[Carbon::parse($offlineActivity[0]['created_at'])->toDateString()])) {
                $data[Carbon::parse($offlineActivity[0]['created_at'])->toDateString()] += $offlineActivity->sum(
                    'duration'
                );
            } else {
                $data[Carbon::parse($offlineActivity[0]['created_at'])->toDateString()] = $offlineActivity->sum(
                    'duration'
                );
            }
        }

        return $data;
    }

    private function getSystemTimeForNurse()
    {
        $this->systemTime = $this->pageTimerData->sum('duration');

        //add manual activities.
        $this->systemTime += $this->activityData
            ->where('logged_from', 'manual_input')
            ->sum('duration');

        $this->activityTime = $this->activityData
            ->sum('duration');

        //percent calc
        if (0 == $this->activityTime || 0 == $this->systemTime) {
            $this->percentTime = 0;
        } else {
            $this->percentTime = round(($this->activityTime / $this->systemTime) * 100, 2);
        }

        //format system time, make to 30 mins if time below 1800
        if (0 != $this->systemTime && null != $this->systemTime) {
            if ($this->systemTime <= 1800) {
                $this->formattedSystemTime = 0.5;
            } else {
                $this->formattedSystemTime = ceil(($this->systemTime * 2) / 3600) / 2;
            }
        } elseif (null == $this->systemTime) {
            $this->formattedSystemTime = 0;
        }
    }
}

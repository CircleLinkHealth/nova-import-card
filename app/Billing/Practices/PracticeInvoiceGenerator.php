<?php

namespace App\Billing\Practices;

use App\Activity;
use App\AppConfig;
use App\Patient;
use App\Practice;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PracticeInvoiceGenerator
{

    private $practice;
    private $month;
    private $patients;

    public function __construct(
        Practice $practice,
        Carbon $month
    ) {

        $this->practice = $practice;
        $this->month = $month;
    }

    public function generatePdf($withItemized = true)
    {

        $pdfInvoice = PDF::loadView('billing.practice.invoice', $this->getInvoiceData());

        $invoiceName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-invoice';

        $pdfInvoice->save(storage_path("download/$invoiceName.pdf"), true);

        $data = [
            'Invoice' => $invoiceName . '.pdf',
        ];

        if ($withItemized) {
            $pdfItemized = PDF::loadView('billing.practice.itemized', $this->getItemizedPatientData());

            $itemizedName = trim($this->practice->name) . '-' . $this->month->toDateString() . '-patients';

            $pdfItemized->save(storage_path("download/$itemizedName.pdf"), true);

            $data['Patient Report'] = $itemizedName . '.pdf';
        }

        $data['practiceId'] = $this->practice->id;

        return $data;
    }

    public function getInvoiceData()
    {

        $practiceId = $this->practice->id;
        $month = $this->month;

        $data['clh_address'] = $this->practice->getAddress();
        $data['bill_to'] = $this->practice->bill_to_name;

        $data['practice'] = $this->practice;
        $data['month'] = $month->format('F, Y');

        $data['rate'] = $this->practice->clh_pppm;
        $data['invoice_num'] = $this->incrementInvoiceNo();

        $data['invoice_date'] = Carbon::today()->toDateString();
        $data['due_by'] = Carbon::today()->addDays($this->practice->term_days)->toDateString();

        $patients = User
            ::whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })
            ->where('program_id', '=', $this->practice->id)
            ->get();

        $data['billable'] = 0;

        foreach ($patients as $patient) {
            $ccm = DB::table('lv_activities')
                ->where('patient_id', $patient->id)
                ->whereBetween('performed_at', [
                    $this->month->firstOfMonth()->startOfDay()->toDateTimeString(),
                    $this->month->endOfMonth()->endOfDay()->toDateTimeString(),
                ])
                ->sum('duration');

            $report = Patient::firstOrCreate([
                'user_id' => $patient->id,
            ])
                ->monthlySummaries()
                ->where('month_year', $this->month->firstOfMonth()->toDateString())
                ->first();

            if ($report && $report->approved == 1 && $ccm > 1199) {
                $data['billable']++;
            }
        }

        $data['invoice_amount'] = number_format(round((double)$this->practice->clh_pppm * $data['billable'], 2), 2);

        return $data;
    }

    public function incrementInvoiceNo()
    {

        $num = AppConfig::where('config_key', 'billing_invoice_count')->first();

        $current = $num['config_value'];

        $num['config_value'] = $num['config_value'] + 1;

        $num->save();

        return $current;
    }

    public function getItemizedPatientData()
    {

        $patients = Patient
            ::whereHas('monthlySummaries', function ($q) {
                $q->where('month_year', $this->month->firstOfMonth()->toDateString())
                    ->where('approved', 1);
            })
            ->whereHas('user', function ($k) {
                $k->where('program_id', $this->practice->id);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        //name, dob, ccm, 2 conditions

        $data = [];
        $data['name'] = $this->practice->display_name;
        $data['month'] = $this->month->toDateString();

        foreach ($patients as $p) {
            $u = $p->user;

            $ccm = DB::table('lv_activities')
                ->where('patient_id', $u->id)
                ->whereBetween('performed_at', [
                    $this->month->firstOfMonth()->startOfDay()->toDateTimeString(),
                    $this->month->endOfMonth()->endOfDay()->toDateTimeString(),
                ])
                ->sum('duration');

            $report = $p->monthlySummaries()
                ->where('month_year', $this->month->firstOfMonth()->toDateString())
                ->first();

            if ($report && $report->approved == 1 && $ccm > 1199) {
                $data['patientData'][$p->user_id]['ccm_time'] = round($ccm / 60, 2);
                $data['patientData'][$p->user_id]['name'] = $u->fullName;
                $data['patientData'][$p->user_id]['dob'] = $u->birth_date;
                $data['patientData'][$p->user_id]['practice'] = $u->primaryPractice->id;
                $data['patientData'][$p->user_id]['provider'] = $u->billingProviderName;

                $data['patientData'][$p->user_id]['problem1'] = $report->billable_problem1;
                $data['patientData'][$p->user_id]['problem1_code'] = $report->billable_problem1_code;
                $data['patientData'][$p->user_id]['problem2'] = $report->billable_problem2;
                $data['patientData'][$p->user_id]['problem2_code'] = $report->billable_problem2_code;
            }
        }

        $data['patientData'] = array_key_exists('patientData', $data)
            ? $this->array_orderby($data['patientData'], 'provider', SORT_ASC, 'name', SORT_ASC)
            : null;

        return $data;
    }

    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field];
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    public function checkForPendingQAForPractice()
    {

        $practice = $this->practice;
        $count = 0;

        $users = User
            ::where('program_id', $practice->id)
            ->whereHas('patientActivities', function ($a) {
                $a->where('performed_at', '>', $this->month->firstOfMonth()->toDateTimeString())
                    ->where('performed_at', '<', $this->month->endOfMonth()->toDateTimeString());
            })
            ->whereHas('roles', function ($r) {
                $r->whereName('participant');
            })
            ->get();

        foreach ($users as $user) {
            $sum = Activity::where('patient_id', $user->id)
                ->where('performed_at', '>', $this->month->firstOfMonth()->toDateTimeString())
                ->where('performed_at', '<', $this->month->endOfMonth()->toDateTimeString())
                ->sum('duration');

            $summary = $user->patientInfo->monthlySummaries()->where(
                'month_year',
                $this->month->firstOfMonth()->toDateString()
            )->first();

            if ($summary == null) {
                continue;
            }

            if ($sum > 1199 && $summary->approved == 0 && $summary->rejected == 0 && $summary->no_of_successful_calls > 0) {
                $count++;
            }
        }

        return ($count > 0)
            ? true
            : false;
    }
}

<?php

namespace App\Billing\Practices;


use App\Activity;
use App\AppConfig;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Patient;
use App\PatientMonthlySummary;
use App\Practice;
use App\User;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PracticeInvoiceGenerator
{

    private $practice;
    private $month;

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

        foreach ($patients as $patient){

            $ccm = DB::table('lv_activities')
                ->where('patient_id', $patient->id)
                ->whereBetween('performed_at', [
                    $this->month->firstOfMonth()->startOfDay()->toDateTimeString(),
                    $this->month->endOfMonth()->endOfDay()->toDateTimeString(),
                ])
                ->sum('duration');

            $report = $patient->patientInfo->patientSummaries()
                ->where('month_year', $this->month->firstOfMonth()->toDateString())
                ->first();

            if($report && $report->approved == 1 && $report->no_of_successful_calls > 0 && $ccm > 1199){

                $data['billable']++;

            }

        }

        $data['invoice_amount'] = $this->practice->clh_pppm * $data['billable'];

        return $data;

    }

    public function getItemizedPatientData()
    {

        $patients = Patient
            ::whereHas('patientSummaries', function ($q) {
                $q->where('month_year', $this->month->firstOfMonth()->toDateString())
                    ->where('no_of_successful_calls', '>', 0)
                    ->where('approved', 1);

            })
                ->whereHas('user', function ($k)
                {
                    $k->where('program_id', $this->practice->id);
                }
            )
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

            if ($ccm < 1200) {
                continue;
            }

            $report = $p->patientSummaries()
                ->where('month_year', $this->month->firstOfMonth()->toDateString())
                ->first();

            $data['patientData'][$p->user_id]['ccm_time'] = round($ccm / 60 , 2);
            $data['patientData'][$p->user_id]['name'] = $u->fullName;
            $data['patientData'][$p->user_id]['dob'] = $u->birth_date;
            $data['patientData'][$p->user_id]['practice'] = $u->primaryPractice->id;
            $data['patientData'][$p->user_id]['provider'] = $u->billingProviderName;

            $data['patientData'][$p->user_id]['problem1'] = $report->billable_problem1;
            $data['patientData'][$p->user_id]['problem1_code'] = $report->billable_problem1_code;
            $data['patientData'][$p->user_id]['problem2'] = $report->billable_problem2;
            $data['patientData'][$p->user_id]['problem2_code'] = $report->billable_problem2_code;

        }

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

            $summary = $user->patientInfo->patientSummaries()->where('month_year',
                $this->month->firstOfMonth()->toDateString())->first();

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
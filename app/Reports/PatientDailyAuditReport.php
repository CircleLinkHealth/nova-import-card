<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports;

use App\Note;
use App\Services\PdfService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/6/17
 * Time: 10:10 AM.
 */
class PatientDailyAuditReport
{
    protected $data = [];
    protected $day;
    protected $patient;

    public function __construct(Patient $patient, Carbon $forMonth)
    {
        $this->patient  = $patient;
        $this->forMonth = $forMonth;
    }

    public function renderData(): array
    {
        $time = Activity::whereBetween('created_at', [
            $this->forMonth->startOfMonth()->toDateTimeString(),
            $this->forMonth->endOfMonth()->toDateTimeString(),
        ])
            ->where('patient_id', $this->patient->user_id)
            ->sum('duration');

        $this->data['name']     = $this->patient->user->getFullName();
        $this->data['month']    = $this->forMonth->format('F, Y');
        $this->data['provider'] = $this->patient->user->getBillingProviderName();
        $this->data['totalCCM'] = $this->formatMonthlyTime($time);

        $activities = DB::table('lv_activities')
            ->select(DB::raw('DATE(performed_at) as day, type, duration'))
            ->whereBetween('created_at', [
                $this->forMonth->startOfMonth()->toDateTimeString(),
                $this->forMonth->endOfMonth()->toDateTimeString(),
            ])
            ->where('patient_id', $this->patient->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $activities          = $activities->groupBy('day');
        $this->data['daily'] = [];

        foreach ($activities as $date => $value) {
            $value = collect($value);

            $this->data['daily'][$date]['activities'] = $value->implode('type', ', ');
            $dailyDuration                            = $value->sum('duration');
            $this->data['daily'][$date]['ccm']        = secondsToMMSS($dailyDuration);

            $notes = Note
                ::wherePatientId($this->patient->user_id)
                    ->whereBetween('created_at', [
                        Carbon::parse($date)->startOfDay()->toDateTimeString(),
                        Carbon::parse($date)->endOfDay()->toDateTimeString(),
                    ])
                    ->get();

            $this->data['daily'][$date]['notes'] = [];

            foreach ($notes as $note) {
                $time                                                        = Carbon::parse($note->created_at)->format('g:i:s A');
                $performer                                                   = User::withTrashed()->find($note->author_id)->getFullName() ?? '';
                $this->data['daily'][$date]['notes'][$note->id]['performer'] = $performer;
                $this->data['daily'][$date]['notes'][$note->id]['time']      = $time;
                $this->data['daily'][$date]['notes'][$note->id]['body']      = $note->body;
            }
        }

        return $this->data;
    }

    public function renderPDF()
    {
        $pdfService = app(PdfService::class);

        $name = $this->patient->user->last_name.'-'.Carbon::now()->timestamp;
        $path = storage_path("download/${name}.pdf");

        $this->renderData();

        $pdf = $pdfService->createPdfFromView('wpUsers.patient.audit', ['data' => $this->data], [], $path);

        $collName = 'audit_report_'.$this->data['month'];

        $this->patient->user->addMedia($path)->preservingOriginal()->toMediaCollection($collName);

        return "/${name}.pdf";
    }

    private function formatMonthlyTime($seconds)
    {
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $H, $i, $s);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports;

use App\Note;
use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
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
    protected $user;

    public function __construct(User $user, Carbon $forMonth)
    {
        $this->user     = $user;
        $this->forMonth = $forMonth;
    }

    public function renderData(): array
    {
        $time = Activity::whereBetween('created_at', [
            $this->forMonth->startOfMonth()->toDateTimeString(),
            $this->forMonth->endOfMonth()->toDateTimeString(),
        ])
            ->where('patient_id', $this->user->id)
            ->sum('duration');

        $this->data['name']     = $this->user->getFullName();
        $this->data['month']    = $this->forMonth->format('F, Y');
        $this->data['provider'] = $this->user->getBillingProviderName();
        $this->data['totalCCM'] = $this->formatMonthlyTime($time);

        $activities = DB::table('lv_activities')
            ->select(DB::raw('DATE(performed_at) as day, type, duration'))
            ->whereBetween('created_at', [
                $this->forMonth->startOfMonth()->toDateTimeString(),
                $this->forMonth->endOfMonth()->toDateTimeString(),
            ])
            ->where('patient_id', $this->user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $activities          = $activities->groupBy('day');
        $this->data['daily'] = [];

        foreach ($activities as $date => $value) {
            $value = collect($value);

            $this->data['daily'][$date]['activities'] = $value->implode('type', ', ');
            $dailyDuration                            = $value->sum('duration');
            $this->data['daily'][$date]['ccm']        = secondsToMMSS($dailyDuration);

            $notes = Note::with(['author'])
                ->wherePatientId($this->user->id)
                ->whereBetween('created_at', [
                    Carbon::parse($date)->startOfDay()->toDateTimeString(),
                    Carbon::parse($date)->endOfDay()->toDateTimeString(),
                ])
                ->get();

            $this->data['daily'][$date]['notes'] = [];

            foreach ($notes as $note) {
                $time                                                        = Carbon::parse($note->created_at)->format('g:i:s A');
                $performer                                                   = $note->author->getFullName() ?? '';
                $this->data['daily'][$date]['notes'][$note->id]['performer'] = $performer;
                $this->data['daily'][$date]['notes'][$note->id]['time']      = $time;
                $this->data['daily'][$date]['notes'][$note->id]['body']      = $note->body;
            }
        }

        return $this->data;
    }

    /**
     * @return bool|string
     */
    public function renderPDF()
    {
        $pdfService = app(PdfService::class);

        $name = $this->user->id.'-'.Carbon::now()->timestamp;
        $path = storage_path("download/${name}.pdf");

        $this->renderData();

        try {
            $pdf = $pdfService->createPdfFromView('wpUsers.patient.audit', ['data' => $this->data], $path);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return false;
        }

        $collName = 'audit_report_'.$this->data['month'];

        $this->user->addMedia($path)->preservingOriginal()->toMediaCollection($collName);

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

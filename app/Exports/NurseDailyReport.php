<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Reports\NurseDailyReport as NurseStatsService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Traits\AttachableAsMedia;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NurseDailyReport implements FromCollection, Responsable, WithHeadings
{
    use AttachableAsMedia;
    use Exportable;
    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var string
     */
    private $filename;

    public function __construct(Carbon $date = null)
    {
        $this->date = $date;
        $this->setFilename();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return NurseStatsService::data($this->date)->map(
            function ($nurseReport) {
                $nurse = User::ofType('care-center')
                    ->with('nurseInfo.workhourables')
                    ->has('nurseInfo')
                    ->find($nurseReport['id']);

                if ( ! $nurse) {
                    \Log::error("User not found: {$nurseReport['name']}");

                    return [];
                }

                $dateClause = function ($q) {
                    return $q->whereBetween('created_at', [
                        $this->date->copy()->startOfDay(),
                        $this->date->copy()->endOfDay(),
                    ]);
                };

                $pageTimers = PageTimer::where('provider_id', $nurse->id)
                    ->select(['id', 'duration', 'created_at'])
                    ->where($dateClause)
                    ->get()
                    ->sum('duration');

                $offlineActivities = Activity::where('provider_id', $nurse->id)
                    ->select(['id', 'duration', 'created_at'])
                    ->where($dateClause)
                    ->where('logged_from', 'manual_input')
                    ->get()
                    ->sum('duration');

                $total = $pageTimers + $offlineActivities;
                $actualHours = round($total / 3600, 1);

                $hoursCommitted = 'N/A';
                if ($nurse->nurseInfo->workhourables && $nurse->nurseInfo->workhourables->count() > 0) {
                    $hoursCommitted = $nurse->nurseInfo->workhourables->first()->{strtolower($this->date->format('l'))};
                }

                return $this->row($nurseReport, $hoursCommitted, $actualHours);
            }
        )->filter()->values();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Time since last activity',
            '# Successful Calls today',
            '# Scheduled calls today',
            '# Completed calls today',
            'CCM mins Today',
            'Last activity',
            'Actual hours worked',
            'Hours Committed',
        ];
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename = null): NurseDailyReport
    {
        if ( ! $filename) {
            $dateString = $this->date->toDateTimeString();
            $filename   = 'Nurse_Daily_Report';
            $now        = now()->toDateTimeString();

            $this->filename = "{$filename}_{$dateString}_created_at_{$now}.xls";

            return $this;
        }

        $this->filename = $filename;

        return $this;
    }

    public function storeAndAttachMediaTo($model)
    {
        $filepath = 'exports/'.$this->getFilename();
        $stored   = $this->store($filepath, 'storage');

        return $this->attachMediaTo($model, storage_path($filepath), "nurse_daily_report_for_{$this->date->toDateString()}");
    }

    /**
     * @param $nurseReport A row from NurseStatsService::data()
     * @param $hoursCommitted integer|string
     * @param $actualHoursWorked integer|string
     *
     * @return array
     */
    private function row($nurseReport, $hoursCommitted, $actualHoursWorked)
    {
        return [
            'name'                     => $nurseReport['name'],
            'Time since last activity' => $nurseReport['Time Since Last Activity'],
            '# Successful Calls today' => $nurseReport['# Successful Calls Today'],
            '# Scheduled calls today'  => $nurseReport['# Scheduled Calls Today'],
            '# Completed calls today'  => $nurseReport['# Completed Calls Today'],
            'CCM mins Today'           => $nurseReport['CCM Mins Today'],
            'Last activity'            => $nurseReport['last_activity'],
            'Actual hours worked'      => $actualHoursWorked
                ?: 'N/A',
            'Hours Committed' => 0 == $hoursCommitted
                ? '0'
                : $hoursCommitted,
        ];
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use App\Reports\NurseDailyReport as NurseStatsService;
use App\Traits\AttachableAsMedia;
use Carbon\Carbon;
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
    use AttachableAsMedia, Exportable;
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
                $fullName = explode(' ', $nurseReport['name']);
                $first = $fullName[0];
                $last = $fullName[1];

                $nurse = User::ofType('care-center')
                    ->with('nurseInfo.workhourables')
                    ->where(
                                 [
                                     ['first_name', '=', $first],
                                     ['last_name', '=', $last],
                                 ]
                             )->has('nurseInfo')
                    ->first();

                if ( ! $nurse) {
                    \Log::error("User not found: {$nurseReport['name']}");

                    return [];
                }

                $pageTimers = PageTimer::where('provider_id', $nurse->id)
                    ->select(['id', 'duration', 'created_at'])
                    ->where(
                                           function ($q) {
                                               $q->where('created_at', '>=', $this->date->copy()->startOfDay())
                                                   ->where('created_at', '<=', $this->date->copy()->endOfDay());
                                           }
                                       )
                    ->get()
                    ->sum('duration');

                $offlineActivities = Activity::where('provider_id', $nurse->id)
                    ->select(['id', 'duration', 'created_at'])
                    ->where(
                                                 function ($q) {
                                                     $q->where('created_at', '>=', $this->date->copy()->startOfDay())
                                                         ->where('created_at', '<=', $this->date->copy()->endOfDay());
                                                 }
                                             )
                    ->where('logged_from', 'manual_input')
                    ->get()
                    ->sum('duration');

                $total = $pageTimers + $offlineActivities;
                $actualHours = round($total / 3600, 1);

                $hoursCommitted = 'N/A';
                if ($nurse->nurseInfo->workhourables && $nurse->nurseInfo->workhourables->count() > 0) {
                    $hoursCommitted = $nurse->nurseInfo->workhourables->first()->{strtolower($this->date->format('l'))};
                }

                return [
                    'name'                     => $nurseReport['name'],
                    'Time since last activity' => $nurseReport['Time Since Last Activity'],
                    '# Successful Calls today' => $nurseReport['# Successful Calls Today'],
                    '# Scheduled calls today'  => $nurseReport['# Scheduled Calls Today'],
                    '# Completed calls today'  => $nurseReport['# Completed Calls Today'],
                    'CCM mins Today'           => $nurseReport['CCM Mins Today'],
                    'Last activity'            => $nurseReport['last_activity'],
                    'Actual hours worked'      => $actualHours
                        ?: 'N/A',
                    'Hours Committed' => 0 == $hoursCommitted
                        ? '0'
                        : $hoursCommitted,
                ];
            }
        )->filter()->values();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return array
     */
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
     *
     * @return NurseDailyReport
     */
    public function setFilename(string $filename = null): NurseDailyReport
    {
        if ( ! $filename) {
            $dateString = $this->date->toDateTimeString();
            $filename   = 'Nurse_Daily_Report';

            $this->filename = "{$filename}_{$dateString}.xls";

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
}

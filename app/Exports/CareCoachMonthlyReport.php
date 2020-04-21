<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CareCoachMonthlyReport implements FromCollection, Responsable, WithHeadings
{
    use Exportable;
    /**
     * @var Carbon
     */
    protected $date;

    /**
     * @var string
     */
    private $fileName;

    public function __construct(Carbon $date)
    {
        $this->date     = $date;
        $this->fileName = "CLH-Nurse-Monthly-Report-$date.xls";
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $fromDate = $this->date->copy()->startOfMonth()->startOfDay();
        $toDate   = $this->date->copy()->endOfMonth()->endOfDay();

        $rows = collect();

        $nurses = User::orderBy('id')
            ->ofType('care-center')
            ->whereHas(
                'activitiesAsProvider',
                function ($a) use ($fromDate, $toDate) {
                    $a->where('performed_at', '>=', $fromDate)
                        ->where('performed_at', '<=', $toDate);
                }
            )
            ->chunk(
                50,
                function ($nurses) use (&$rows, $fromDate, $toDate) {
                    foreach ($nurses as $nurse) {
                        $seconds = Activity::where('provider_id', $nurse->id)
                            ->where(
                                function ($q) use ($fromDate, $toDate) {
                                    $q->where('performed_at', '>=', $fromDate)
                                        ->where('performed_at', '<=', $toDate);
                                }
                            )
                            ->sum('duration');

                        if (0 == $seconds) {
                            continue;
                        }

                        $rows->push(
                            $this->row($nurse, $seconds)
                        );
                    }
                }
            );

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Nurse',
            'CCM Time (HH:MM:SS)',
        ];
    }

    /**
     * @param $seconds
     */
    private function row(User $nurse, $seconds): array
    {
        return [
            'Nurse'               => $nurse->display_name,
            'CCM Time (HH:MM:SS)' => gmdate('H:i:s', $seconds),
        ];
    }
}

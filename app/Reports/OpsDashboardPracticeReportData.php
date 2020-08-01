<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Support\Collection;

/**
 * This class describes the Ops Dashboard Report data for each practice.
 * The array from toArray() method will be saved for each practice for each date on: ops_dashboard_practice_reports.data (JSON).
 */
class OpsDashboardPracticeReportData
{
    const FIFTEEN_MINUTES = 900;

    const FIVE_MINUTES = 300;

    const TEN_MINUTES = 600;

    const TWENTY_MINUTES = 1200;

    public $addedCount                        = 0;
    public $addedIds                          = [];
    public $deletedCount                      = 0;
    public $deletedIds                        = [];
    public $enrolledPatientIds                = [];
    public $fifteenToTwentyMinsCount          = 0;
    public $fiveToTenMinsCount                = 0;
    public $g0506ToEnrollCount                = 0;
    public $g0506ToEnrollIds                  = [];
    public $lostAddedCalculatedUsingRevisions = false;
    public $pausedCount                       = 0;
    public $pausedIds                         = [];
    public $priorDayReportUpdatedAt           = 'N/A';
    public $priorDayTotals                    = 'N/A';
    public $reportUpdatedAt                   = 'N/A';
    public $revisionsAddedCount               = 0;
    public $revisionsAddedIds                 = [];
    public $revisionsPausedCount              = 0;
    public $revisionsPausedIds                = [];
    public $revisionsUnreachableCount         = 0;
    public $revisionsUnreachableIds           = [];
    public $revisionsWithdrawnCount           = 0;
    public $revisionsWithdrawnIds             = [];
    public $tenToFifteenMinsCount             = 0;
    public $totalCcmTimeArray                 = [];
    public $totalCount                        = 0;
    public $totalPausedCount                  = 0;
    public $totalUnreachableCount             = 0;
    public $totalWithdrawnCount               = 0;
    public $twentyPlusBhiMinsCount            = 0;
    public $twentyPlusMinsCount               = 0;
    public $unreachableCount                  = 0;
    public $unreachableIds                    = [];
    public $withdrawnCount                    = 0;
    public $withdrawnIds                      = [];
    public $zeroMinsCount                     = 0;
    public $zeroToFiveMinsCount               = 0;

    public function addedCountIsMatching()
    {
        return $this->addedCount === $this->revisionsAddedCount;
    }

    /**
     * @return mixed
     */
    public function getDelta()
    {
        $delta = $this->addedCount - $this->pausedCount - $this->withdrawnCount - $this->unreachableCount - $this->deletedCount;

        return $delta ?? 0;
    }

    /**
     * @return mixed
     */
    public function getReportUpdatedAt()
    {
        return Carbon::now()->toDateTimeString();
    }

    public function getTotalPausedCount()
    {
        return count($this->pausedIds);
    }

    public function getTotalUnreachableCount()
    {
        return count($this->unreachableIds);
    }

    public function getTotalWithdrawnCount()
    {
        return count($this->withdrawnIds);
    }

    public function incrementAddedCount(): void
    {
        ++$this->addedCount;
    }

    public function incrementDeletedCount(): void
    {
        ++$this->deletedCount;
    }

    public function incrementFifteenToTwentyMinsCount(): void
    {
        ++$this->fifteenToTwentyMinsCount;
    }

    public function incrementFiveToTenMinsCount(): void
    {
        ++$this->fiveToTenMinsCount;
    }

    public function incrementG0506ToEnrollCount(): void
    {
        ++$this->g0506ToEnrollCount;
    }

    public function incrementPausedCount(): void
    {
        ++$this->pausedCount;
    }

    public function incrementRevisionsAddedCount()
    {
        ++$this->revisionsAddedCount;
    }

    public function incrementRevisionsPausedCount()
    {
        ++$this->revisionsPausedCount;
    }

    public function incrementRevisionsUnreachableCount()
    {
        ++$this->revisionsUnreachableCount;
    }

    public function incrementRevisionsWithdrawnCount()
    {
        ++$this->revisionsWithdrawnCount;
    }

    public function incrementTenToFifteenMinsCount(): void
    {
        ++$this->tenToFifteenMinsCount;
    }

    public function incrementTimeRangeCount(PatientMonthlySummary $pms)
    {
        $ccmTime = $pms->ccm_time;
        $bhiTime = $pms->bhi_time;

        if (0 === $ccmTime || null == $ccmTime) {
            $this->incrementZeroMinsCount();
        }
        if ($ccmTime > 0 and $ccmTime < self::FIVE_MINUTES) {
            $this->incrementZeroToFiveMinsCount();
        }
        if ($ccmTime >= self::FIVE_MINUTES and $ccmTime < self::TEN_MINUTES) {
            $this->incrementFiveToTenMinsCount();
        }
        if ($ccmTime >= self::TEN_MINUTES and $ccmTime < self::FIFTEEN_MINUTES) {
            $this->incrementTenToFifteenMinsCount();
        }
        if ($ccmTime >= self::FIFTEEN_MINUTES and $ccmTime < $this::TWENTY_MINUTES) {
            $this->incrementFifteenToTwentyMinsCount();
        }
        if ($ccmTime >= $this::TWENTY_MINUTES) {
            $this->incrementTwentyPlusMinsCount();
        }
        if ($bhiTime >= $this::TWENTY_MINUTES) {
            $this->incrementTwentyPlusBhiMinsCount();
        }
    }

    public function incrementTotalCount(): void
    {
        ++$this->totalCount;
    }

    public function incrementTwentyPlusBhiMinsCount(): void
    {
        ++$this->twentyPlusBhiMinsCount;
    }

    public function incrementTwentyPlusMinsCount(): void
    {
        ++$this->twentyPlusMinsCount;
    }

    public function incrementUnreachableCount(): void
    {
        ++$this->unreachableCount;
    }

    public function incrementWithdrawnCount(): void
    {
        ++$this->withdrawnCount;
    }

    public function incrementZeroMinsCount(): void
    {
        ++$this->zeroMinsCount;
    }

    public function incrementZeroToFiveMinsCount(): void
    {
        ++$this->zeroToFiveMinsCount;
    }

    public function pausedCountIsMatching()
    {
        return $this->pausedCount === $this->revisionsPausedCount;
    }

    public function setDeltasUsingRevisionCounts()
    {
        $this->addedCount       = $this->revisionsAddedCount;
        $this->pausedCount      = $this->revisionsPausedCount;
        $this->withdrawnCount   = $this->revisionsWithdrawnCount;
        $this->unreachableCount = $this->revisionsUnreachableCount;

        $this->lostAddedCalculatedUsingRevisions = true;
    }

    public function toArray(): array
    {
        return [
            //how many patients are in each CCM or BHI time category
            '0 mins'  => $this->zeroMinsCount,
            '0-5'     => $this->zeroToFiveMinsCount,
            '5-10'    => $this->fiveToTenMinsCount,
            '10-15'   => $this->tenToFifteenMinsCount,
            '15-20'   => $this->fifteenToTwentyMinsCount,
            '20+'     => $this->twentyPlusMinsCount,
            '20+ BHI' => $this->twentyPlusBhiMinsCount,
            //total enrolled patients
            'Total'            => $this->totalCount,
            'Prior Day totals' => $this->priorDayTotals,
            //How many patients have been added or lost for this date
            'Added'       => $this->addedCount,
            'Paused'      => $this->pausedCount,
            'Unreachable' => $this->unreachableCount,
            'Withdrawn'   => $this->withdrawnCount,
            'Deleted'     => $this->deletedCount,
            //all patients added minus all patients lost
            'Delta'           => $this->getDelta(),
            'G0506 To Enroll' => $this->g0506ToEnrollCount,
            //added to help us generate next day's report - so we don't rely on revisions

            'total_paused_count'      => $this->getTotalPausedCount(),
            'total_unreachable_count' => $this->getTotalUnreachableCount(),
            'total_withdrawn_count'   => $this->getTotalWithdrawnCount(),

            'prior_day_report_updated_at' => $this->priorDayReportUpdatedAt,
            'report_updated_at'           => $this->getReportUpdatedAt(),
            //will help us produce accurate deltas when comparing last day with current day totals per status
            'enrolled_patient_ids' => $this->enrolledPatientIds,
            //adding to help us generate hours behind metric,
            'total_ccm_time' => $this->getTotalCcmTime(),
            //bool - show tooltip to let admin know how calculations were generated
            'lost_added_calculated_using_revisions' => $this->lostAddedCalculatedUsingRevisions,

            //to help us debug
            //delta ids
            'added_ids'       => $this->addedIds,
            'deleted_ids'     => $this->deletedIds,
            'withdrawn_ids'   => $this->withdrawnIds,
            'paused_ids'      => $this->pausedIds,
            'unreachable_ids' => $this->unreachableIds,

            //revision info
            'revisions_added_ids'       => $this->revisionsAddedIds,
            'revisions_paused_ids'      => $this->revisionsPausedIds,
            'revisions_withdrawn_ids'   => $this->revisionsWithdrawnIds,
            'revisions_unreachable_ids' => $this->revisionsUnreachableIds,

            'revisions_added_count'       => $this->revisionsAddedCount,
            'revisions_paused_count'      => $this->revisionsPausedCount,
            'revisions_withdrawn_count'   => $this->revisionsWithdrawnCount,
            'revisions_unreachable_count' => $this->revisionsUnreachableCount,
        ];
    }

    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }

    public function totalsAreMatching()
    {
        return $this->totalCount - $this->getDelta() === $this->priorDayTotals;
    }

    public function unreachableCountIsMatching()
    {
        return $this->unreachableCount === $this->revisionsUnreachableCount;
    }

    public function withdrawnCountIsMatching()
    {
        return $this->withdrawnCount === $this->revisionsWithdrawnCount;
    }

    private function getTotalCcmTime()
    {
        return array_sum($this->totalCcmTimeArray);
    }
}

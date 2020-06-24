<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * This class describes the Ops Dashboard Report data for each practice.
 * The array from toArray() method will be saved for each practice for each date on: ops_dashboard_practice_reports.data (JSON).
 */
class OpsDashboardPracticeReportData
{
    protected $added    = 0;
    protected $addedIds = [];
    protected $enrolledPatientIds;
    protected $fifteenToTwentyMins               = 0;
    protected $fiveToTenMins                     = 0;
    protected $g0506ToEnroll                     = 0;
    protected $lostAddedCalculatedUsingRevisions = false;
    protected $paused                            = 0;
    protected $pausedIds                         = [];
    protected $priorDayReportUpdatedAt;
    protected $priorDayTotals;
    protected $reportUpdatedAt;
    protected $revisionsAddedCount       = 0;
    protected $revisionsAddedIds         = [];
    protected $revisionsPausedCount      = 0;
    protected $revisionsPausedIds        = [];
    protected $revisionsUnreachableCount = 0;
    protected $revisionsUnreachableIds   = [];
    protected $revisionsWithdrawnCount   = 0;
    protected $revisionsWithdrawnIds     = [];
    protected $tenToFifteenMins          = 0;
    protected $total                     = 0;
    protected $totalCcmTime;
    protected $totalPausedCount      = 0;
    protected $totalUnreachableCount = 0;
    protected $totalWithdrawnCount   = 0;
    protected $twentyPlusBhiMins     = 0;
    protected $twentyPlusMins        = 0;
    protected $unreachable           = 0;
    protected $unreachableIds        = [];
    protected $withdrawn             = 0;
    protected $withdrawnIds          = [];
    protected $zeroMins              = 0;
    protected $zeroToFiveMins        = 0;

    /**
     * @return mixed
     */
    public function getAdded()
    {
        return $this->added ?? 0;
    }

    public function getAddedIds(): array
    {
        return $this->addedIds;
    }

    /**
     * @return mixed
     */
    public function getDelta()
    {
        $delta = $this->getAdded() - $this->getPaused() - $this->getWithdrawn() - $this->getUnreachable();

        return $delta ?? 0;
    }

    /**
     * @return mixed
     */
    public function getEnrolledPatientIds()
    {
        return $this->enrolledPatientIds ?? [];
    }

    /**
     * @return mixed
     */
    public function getFifteenToTwentyMins()
    {
        return $this->fifteenToTwentyMins ?? 0;
    }

    /**
     * @return mixed
     */
    public function getFiveToTenMins()
    {
        return $this->fiveToTenMins ?? 0;
    }

    /**
     * @return mixed
     */
    public function getG0506ToEnroll()
    {
        return $this->g0506ToEnroll ?? 0;
    }

    public function getLostAddedCalculatedUsingRevisions(): bool
    {
        return $this->lostAddedCalculatedUsingRevisions;
    }

    /**
     * @return mixed
     */
    public function getPaused()
    {
        return $this->paused ?? 0;
    }

    public function getPausedIds(): array
    {
        return $this->pausedIds;
    }

    /**
     * @return mixed
     */
    public function getPriorDayReportUpdatedAt()
    {
        return $this->priorDayReportUpdatedAt ?? 'N/A';
    }

    /**
     * Prior day totals should be manually set by last day's report
     * However if for some reason that data is not available, calculate using deltas.
     *
     * @return mixed
     */
    public function getPriorDayTotals()
    {
        return $this->priorDayTotals ?? 0;
    }

    /**
     * @return mixed
     */
    public function getReportUpdatedAt()
    {
        return Carbon::now()->toDateTimeString();
    }

    public function getRevisionsAddedCount(): int
    {
        return $this->revisionsAddedCount;
    }

    public function getRevisionsAddedIds(): array
    {
        return $this->revisionsAddedIds;
    }

    public function getRevisionsPausedCount(): int
    {
        return $this->revisionsPausedCount;
    }

    public function getRevisionsPausedIds(): array
    {
        return $this->revisionsPausedIds;
    }

    public function getRevisionsUnreachableCount(): int
    {
        return $this->revisionsUnreachableCount;
    }

    public function getRevisionsUnreachableIds(): array
    {
        return $this->revisionsUnreachableIds;
    }

    public function getRevisionsWithdrawnCount(): int
    {
        return $this->revisionsWithdrawnCount;
    }

    public function getRevisionsWithdrawnIds(): array
    {
        return $this->revisionsWithdrawnIds;
    }

    /**
     * @return mixed
     */
    public function getTenToFifteenMins()
    {
        return $this->tenToFifteenMins ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTotalCcmTime()
    {
        return $this->totalCcmTime ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTotalPausedCount()
    {
        return $this->totalPausedCount ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTotalUnreachableCount()
    {
        return $this->totalUnreachableCount ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTotalWithdrawnCount()
    {
        return $this->totalWithdrawnCount ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTwentyPlusBhiMins()
    {
        return $this->twentyPlusBhiMins ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTwentyPlusMins()
    {
        return $this->twentyPlusMins ?? 0;
    }

    /**
     * @return mixed
     */
    public function getUnreachable()
    {
        return $this->unreachable ?? 0;
    }

    public function getUnreachableIds(): array
    {
        return $this->unreachableIds;
    }

    /**
     * @return mixed
     */
    public function getWithdrawn()
    {
        return $this->withdrawn ?? 0;
    }

    public function getWithdrawnIds(): array
    {
        return $this->withdrawnIds;
    }

    /**
     * @return mixed
     */
    public function getZeroMins()
    {
        return $this->zeroMins ?? 0;
    }

    /**
     * @return mixed
     */
    public function getZeroToFiveMins()
    {
        return $this->zeroToFiveMins ?? 0;
    }

    public function incrementAdded(): void
    {
        ++$this->added;
    }

    public function incrementFifteenToTwentyMins(): void
    {
        ++$this->fifteenToTwentyMins;
    }

    public function incrementFiveToTenMins(): void
    {
        ++$this->fiveToTenMins;
    }

    public function incrementG0506ToEnroll(): void
    {
        ++$this->g0506ToEnroll;
    }

    public function incrementPaused(): void
    {
        ++$this->paused;
    }

    public function incrementTenToFifteenMins(): void
    {
        ++$this->tenToFifteenMins;
    }

    public function incrementTotal(): void
    {
        ++$this->total;
    }

    public function incrementTwentyPlusBhiMins(): void
    {
        ++$this->twentyPlusBhiMins;
    }

    public function incrementTwentyPlusMins(): void
    {
        ++$this->twentyPlusMins;
    }

    public function incrementUnreachable(): void
    {
        ++$this->unreachable;
    }

    public function incrementWithdrawn(): void
    {
        ++$this->withdrawn;
    }

    public function incrementZeroMins(): void
    {
        ++$this->zeroMins;
    }

    public function incrementZeroToFiveMins(): void
    {
        ++$this->zeroToFiveMins;
    }

    /**
     * @param mixed $added
     */
    public function setAdded($added): void
    {
        $this->added = $added;
    }

    public function setAddedIds(array $addedIds): void
    {
        $this->addedIds = $addedIds;
    }

    /**
     * @param mixed $enrolledPatientIds
     */
    public function setEnrolledPatientIds($enrolledPatientIds): void
    {
        $this->enrolledPatientIds = $enrolledPatientIds;
    }

    /**
     * @param mixed $fifteenToTwentyMins
     */
    public function setFifteenToTwentyMins($fifteenToTwentyMins): void
    {
        $this->fifteenToTwentyMins = $fifteenToTwentyMins;
    }

    /**
     * @param mixed $fiveToTenMins
     */
    public function setFiveToTenMins($fiveToTenMins): void
    {
        $this->fiveToTenMins = $fiveToTenMins;
    }

    /**
     * @param mixed $g0506ToEnroll
     */
    public function setG0506ToEnroll($g0506ToEnroll): void
    {
        $this->g0506ToEnroll = $g0506ToEnroll;
    }

    public function setLostAddedCalculatedUsingRevisions(bool $lostAddedCalculatedUsingRevisions): void
    {
        $this->lostAddedCalculatedUsingRevisions = $lostAddedCalculatedUsingRevisions;
    }

    /**
     * @param mixed $paused
     */
    public function setPaused($paused): void
    {
        $this->paused = $paused;
    }

    public function setPausedIds(array $pausedIds): void
    {
        $this->pausedIds = $pausedIds;
    }

    /**
     * @param mixed $priorDayReportUpdatedAt
     */
    public function setPriorDayReportUpdatedAt($priorDayReportUpdatedAt): void
    {
        $this->priorDayReportUpdatedAt = $priorDayReportUpdatedAt;
    }

    /**
     * @param mixed $priorDayTotals
     */
    public function setPriorDayTotals($priorDayTotals): void
    {
        $this->priorDayTotals = $priorDayTotals;
    }

    /**
     * @param mixed $reportUpdatedAt
     */
    public function setReportUpdatedAt($reportUpdatedAt): void
    {
        $this->reportUpdatedAt = $reportUpdatedAt;
    }

    public function setRevisionsAddedCount(int $revisionsAddedCount): void
    {
        $this->revisionsAddedCount = $revisionsAddedCount;
    }

    public function setRevisionsAddedIds(array $revisionsAddedIds): void
    {
        $this->revisionsAddedIds = $revisionsAddedIds;
    }

    public function setRevisionsPausedCount(int $revisionsPausedCount): void
    {
        $this->revisionsPausedCount = $revisionsPausedCount;
    }

    public function setRevisionsPausedIds(array $revisionsPausedIds): void
    {
        $this->revisionsPausedIds = $revisionsPausedIds;
    }

    public function setRevisionsUnreachableCount(int $revisionsUnreachableCount): void
    {
        $this->revisionsUnreachableCount = $revisionsUnreachableCount;
    }

    public function setRevisionsUnreachableIds(array $revisionsUnreachableIds): void
    {
        $this->revisionsUnreachableIds = $revisionsUnreachableIds;
    }

    public function setRevisionsWithdrawnCount(int $revisionsWithdrawnCount): void
    {
        $this->revisionsWithdrawnCount = $revisionsWithdrawnCount;
    }

    public function setRevisionsWithdrawnIds(array $revisionsWithdrawnIds): void
    {
        $this->revisionsWithdrawnIds = $revisionsWithdrawnIds;
    }

    /**
     * @param mixed $tenToFifteenMins
     */
    public function setTenToFifteenMins($tenToFifteenMins): void
    {
        $this->tenToFifteenMins = $tenToFifteenMins;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;
    }

    /**
     * @param mixed $totalCcmTime
     */
    public function setTotalCcmTime($totalCcmTime): void
    {
        $this->totalCcmTime = $totalCcmTime;
    }

    /**
     * @param mixed $totalPausedCount
     */
    public function setTotalPausedCount($totalPausedCount): void
    {
        $this->totalPausedCount = $totalPausedCount;
    }

    /**
     * @param mixed $totalUnreachableCount
     */
    public function setTotalUnreachableCount($totalUnreachableCount): void
    {
        $this->totalUnreachableCount = $totalUnreachableCount;
    }

    /**
     * @param mixed $totalWithdrawnCount
     */
    public function setTotalWithdrawnCount($totalWithdrawnCount): void
    {
        $this->totalWithdrawnCount = $totalWithdrawnCount;
    }

    /**
     * @param mixed $twentyPlusBhiMins
     */
    public function setTwentyPlusBhiMins($twentyPlusBhiMins): void
    {
        $this->twentyPlusBhiMins = $twentyPlusBhiMins;
    }

    /**
     * @param mixed $twentyPlusMins
     */
    public function setTwentyPlusMins($twentyPlusMins): void
    {
        $this->twentyPlusMins = $twentyPlusMins;
    }

    /**
     * @param mixed $unreachable
     */
    public function setUnreachable($unreachable): void
    {
        $this->unreachable = $unreachable;
    }

    public function setUnreachableIds(array $unreachableIds): void
    {
        $this->unreachableIds = $unreachableIds;
    }

    /**
     * @param mixed $withdrawn
     */
    public function setWithdrawn($withdrawn): void
    {
        $this->withdrawn = $withdrawn;
    }

    public function setWithdrawnIds(array $withdrawnIds): void
    {
        $this->withdrawnIds = $withdrawnIds;
    }

    /**
     * @param mixed $zeroMins
     */
    public function setZeroMins($zeroMins): void
    {
        $this->zeroMins = $zeroMins;
    }

    /**
     * @param mixed $zeroToFiveMins
     */
    public function setZeroToFiveMins($zeroToFiveMins): void
    {
        $this->zeroToFiveMins = $zeroToFiveMins;
    }

    public function toArray(): array
    {
        return [
            //how many patients are in each CCM or BHI time category
            '0 mins'  => $this->getZeroMins(),
            '0-5'     => $this->getZeroToFiveMins(),
            '5-10'    => $this->getFiveToTenMins(),
            '10-15'   => $this->getTenToFifteenMins(),
            '15-20'   => $this->getFifteenToTwentyMins(),
            '20+'     => $this->getTwentyPlusMins(),
            '20+ BHI' => $this->getTwentyPlusBhiMins(),
            //total enrolled patients
            'Total'            => $this->getTotal(),
            'Prior Day totals' => $this->getPriorDayTotals(),
            //How many patients have been added or lost for this date
            'Added'       => $this->getAdded(),
            'Paused'      => $this->getPaused(),
            'Unreachable' => $this->getUnreachable(),
            'Withdrawn'   => $this->getWithdrawn(),
            //all patients added minus all patients lost
            'Delta'           => $this->getDelta(),
            'G0506 To Enroll' => $this->getG0506ToEnroll(),
            //added to help us generate next day's report - so we don't rely on revisions

            'total_paused_count'      => $this->getTotalPausedCount(),
            'total_unreachable_count' => $this->getTotalUnreachableCount(),
            'total_withdrawn_count'   => $this->getTotalWithdrawnCount(),

            'prior_day_report_updated_at' => $this->getPriorDayReportUpdatedAt(),
            'report_updated_at'           => $this->getReportUpdatedAt(),
            //will help us produce accurate deltas when comparing last day with current day totals per status
            'enrolled_patient_ids' => $this->getEnrolledPatientIds(),
            //adding to help us generate hours behind metric,
            'total_ccm_time'                        => $this->getTotalCcmTime(),
            'lost_added_calculated_using_revisions' => $this->getLostAddedCalculatedUsingRevisions(),

            //to help us debug
            //delta ids
            'added_ids'       => $this->getAddedIds(),
            'withdrawn_ids'   => $this->getWithdrawnIds(),
            'paused_ids'      => $this->getPausedIds(),
            'unreachable_ids' => $this->getUnreachableIds(),

            //revision info
            'revisions_added_ids'       => $this->getRevisionsAddedIds(),
            'revisions_paused_ids'      => $this->getRevisionsPausedIds(),
            'revisions_withdrawn_ids'   => $this->getRevisionsWithdrawnIds(),
            'revisions_unreachable_ids' => $this->getRevisionsUnreachableIds(),

            'revisions_added_count'       => $this->getRevisionsAddedCount(),
            'revisions_paused_count'      => $this->getRevisionsPausedCount(),
            'revisions_withdrawn_count'   => $this->getRevisionsWithdrawnCount(),
            'revisions_unreachable_count' => $this->getRevisionsUnreachableCount(),
        ];
    }

    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }
}

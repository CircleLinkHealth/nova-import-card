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
    protected $added;
    protected $enrolledPatientIds;
    protected $fifteenToTwentyMins;
    protected $fiveToTenMins;
    protected $g0506ToEnroll;
    protected $paused;
    protected $priorDayReportUpdatedAt;
    protected $priorDayTotals;
    protected $reportUpdatedAt;
    protected $tenToFifteenMins;

    protected $total;
    protected $totalCcmTime;
    protected $totalPausedCount;
    protected $totalUnreachableCount;
    protected $totalWithdrawnCount;
    protected $twentyPlusBhiMins;
    protected $twentyPlusMins;
    protected $unreachable;
    protected $withdrawn;
    protected $zeroMins;
    protected $zeroToFiveMins;

    /**
     * @return mixed
     */
    public function getAdded()
    {
        return $this->added ?? 0;
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

    /**
     * @return mixed
     */
    public function getPaused()
    {
        return $this->paused ?? 0;
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
        if ( ! $this->priorDayTotals) {
            $this->priorDayTotals = $this->getTotal() - $this->getDelta();
        }

        return $priorDayTotals ?? 0;
    }

    /**
     * @return mixed
     */
    public function getReportUpdatedAt()
    {
        return Carbon::now()->toDateString();
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

    /**
     * @return mixed
     */
    public function getWithdrawn()
    {
        return $this->withdrawn ?? 0;
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
        if ( ! $this->added) {
            $this->setAdded(1);

            return;
        }
        ++$this->added;
    }

    public function incrementFifteenToTwentyMins(): void
    {
        if ( ! $this->fifteenToTwentyMins) {
            $this->setFifteenToTwentyMins(1);

            return;
        }
        ++$this->fifteenToTwentyMins;
    }

    public function incrementFiveToTenMins(): void
    {
        if ( ! $this->fiveToTenMins) {
            $this->setFiveToTenMins(1);

            return;
        }
        ++$this->fiveToTenMins;
    }

    public function incrementG0506ToEnroll(): void
    {
        if ( ! $this->g0506ToEnroll) {
            $this->setG0506ToEnroll(1);

            return;
        }
        ++$this->g0506ToEnroll;
    }

    public function incrementPaused(): void
    {
        if ( ! $this->paused) {
            $this->setPaused(1);

            return;
        }
        ++$this->paused;
    }

    public function incrementTenToFifteenMins(): void
    {
        if ( ! $this->tenToFifteenMins) {
            $this->setTenToFifteenMins(1);

            return;
        }
        ++$this->tenToFifteenMins;
    }

    public function incrementTotal(): void
    {
        if ( ! $this->total) {
            $this->setTotal(1);

            return;
        }
        ++$this->total;
    }

    public function incrementTwentyPlusBhiMins(): void
    {
        if ( ! $this->twentyPlusBhiMins) {
            $this->setTwentyPlusBhiMins(1);

            return;
        }
        ++$this->twentyPlusBhiMins;
    }

    public function incrementTwentyPlusMins(): void
    {
        if ( ! $this->twentyPlusMins) {
            $this->setTwentyPlusMins(1);

            return;
        }
        ++$this->twentyPlusMins;
    }

    public function incrementUnreachable(): void
    {
        if ( ! $this->unreachable) {
            $this->setUnreachable(1);

            return;
        }
        ++$this->unreachable;
    }

    public function incrementWithdrawn(): void
    {
        if ( ! $this->withdrawn) {
            $this->setWithdrawn(1);

            return;
        }
        ++$this->withdrawn;
    }

    public function incrementZeroMins(): void
    {
        if ( ! $this->zeroMins) {
            $this->setZeroMins(1);

            return;
        }
        ++$this->zeroMins;
    }

    public function incrementZeroToFiveMins(): void
    {
        if ( ! $this->zeroToFiveMins) {
            $this->setZeroToFiveMins(1);

            return;
        }
        ++$this->zeroToFiveMins;
    }

    /**
     * @param mixed $added
     */
    public function setAdded($added): void
    {
        $this->added = $added;
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

    /**
     * @param mixed $paused
     */
    public function setPaused($paused): void
    {
        $this->paused = $paused;
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

    /**
     * @param mixed $withdrawn
     */
    public function setWithdrawn($withdrawn): void
    {
        $this->withdrawn = $withdrawn;
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
            'total_ccm_time' => $this->getTotalCcmTime(),
        ];
    }

    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }
}

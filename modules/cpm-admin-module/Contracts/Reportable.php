<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Contracts;

use Carbon\Carbon;

interface Reportable
{
    /**
     * Sum of activity time for this Reportable.
     *
     * @return mixed
     */
    public function activitiesDuration(Carbon $start, Carbon $end);

    /**
     * Total eligible-to-be-billed patients count (for given month) for this Reportable.
     *
     * @return mixed
     */
    public function billablePatientsCountForMonth(Carbon $month);

    /**
     * Call count for this Reportable.
     *
     * @param null $status
     *
     * @return mixed
     */
    public function callCount(Carbon $start, Carbon $end, $status = null);

    /**
     * Forwarded emergency notes count for this Reportable.
     *
     * @return mixed
     */
    public function forwardedEmergencyNotesCount(Carbon $start, Carbon $end);

    /**
     * Forwarded notes count for this Reportable.
     *
     * @return mixed
     */
    public function forwardedNotesCount(Carbon $start, Carbon $end);

    /**
     * The link to view this Reportable's notes.
     *
     * @return mixed
     */
    public function linkToNotes();

    /**
     * Observation count for this Reportable.
     *
     * @return mixed
     */
    public function observationsCount(Carbon $start, Carbon $end);

    /**
     * All patients for this Reportable.
     *
     * @return mixed
     */
    public function patients();

    /**
     * Total billed patients count (since the beginning of time) for this Reportable.
     *
     * @return mixed
     */
    public function totalBilledPatientsCount(Carbon $month = null);
}

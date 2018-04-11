<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 08/17/2017
 * Time: 11:24 AM
 */

namespace App\Contracts\Reports;

use Carbon\Carbon;

interface Reportable
{
    /**
     * All patients for this Reportable.
     *
     * @return mixed
     */
    public function patients();

    /**
     * Call count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param null $status
     *
     * @return mixed
     */
    public function callCount(Carbon $start, Carbon $end, $status = null);

    /**
     * Sum of activity time for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function activitiesDuration(Carbon $start, Carbon $end);

    /**
     * Observation count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function observationsCount(Carbon $start, Carbon $end);

    /**
     * Forwarded notes count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function forwardedNotesCount(Carbon $start, Carbon $end);

    /**
     * Forwarded emergency notes count for this Reportable.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return mixed
     */
    public function forwardedEmergencyNotesCount(Carbon $start, Carbon $end);

    /**
     * Total billed patients count (since the beginning of time) for this Reportable.
     *
     * @param Carbon|null $month
     *
     * @return mixed
     */
    public function totalBilledPatientsCount(Carbon $month = null);

    /**
     * Total eligible-to-be-billed patients count (for given month) for this Reportable.
     *
     * @param Carbon $month
     *
     * @return mixed
     */
    public function billablePatientsCountForMonth(Carbon $month);

    /**
     * The link to view this Reportable's notes.
     *
     * @return mixed
     */
    public function linkToNotes();
}

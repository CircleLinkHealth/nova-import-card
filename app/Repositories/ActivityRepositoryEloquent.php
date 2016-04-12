<?php

namespace App\Repositories;

use App\CcmTimeApiLog;
use App\User;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\ActivityRepository;
use App\Activity;
use App\Validators\ActivityValidator;

/**
 * Class ActivityRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ActivityRepositoryEloquent extends BaseRepository implements ActivityRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Activity::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * Get all CCM Activities
     * Query by ProviderId, PatientId and Dates
     * Set sendAll to true to return all activities regardless of dates.
     *
     * @param $patientId
     * @param $providerId
     * @param $startDate
     * @param $endDate
     * @param bool $sendAll
     * @return mixed
     */
    public function getCcmActivities($patientId, $providerId, $startDate, $endDate, $sendAll = false)
    {
        //Dynamically get all the tables' names since we'll probably change them soon
        $activitiesTable = (new Activity())->getTable();
        $userTable = (new User())->getTable();

        $activities = Activity::select(DB::raw("
                $activitiesTable.id as id,
                type as commentString,
                duration as length,
                duration_unit as lengthUnit,
                $userTable.display_name as servicePerson,
                $activitiesTable.performed_at as startingDateTime
                "))
            ->whereProviderId($providerId)
            ->wherePatientId($patientId)
            ->join($userTable, "$userTable.ID", '=', "$activitiesTable.provider_id")
            ->whereBetween("$activitiesTable.performed_at", [
                $startDate, $endDate
            ]);
        if (!$sendAll) {
            $activities->whereNotIn("$activitiesTable.id", CcmTimeApiLog::lists('activity_id'));
        }
        $activities = $activities->get();

        return $activities;
    }
}

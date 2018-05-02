<?php

namespace App\Filters;


use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NurseFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        return [
            'status' => 'active',
            'user'   => true,
        ];
    }

    /**
     * Scope for active nurses.
     *
     * @param string $status
     *
     * @return Builder
     */
    public function active($status = 'active')
    {
        return $this->status($status);
    }

    /**
     * Scope nurses by status.
     *
     * @param string $status
     *
     * @return Builder
     */
    public function status($status = 'active')
    {
        return $this->builder->where('status', '=', $status);
    }

    /**
     * Get nurses that are licenced in any of the states provided.
     *
     * @param string $states Comma delimited State Codes. Example: 'NJ, NY, GA'
     * @param string $operator Can 'and' or 'or'
     *
     * @return Builder
     */
    public function statesOr($states = null, $operator = 'or')
    {
        return $this->states($states, $operator);
    }

    /**
     * Get the states the nurse is licenced in.
     * By default the and operator is selected, which means that only nurses that include all states will be included.
     *
     * @param string $states Comma delimited State Codes. Example: 'NJ, NY, GA'
     * @param string $operator Can 'and' or 'or'
     *
     * @return Builder
     */
    public function states($states = null, $operator = 'and')
    {
        if ( ! $states) {
            return $this->builder->with('states');
        }

        if (str_contains($states, ',')) {
            $states = explode(',', $states);
        }

        if ( ! is_array($states)) {
            $states = [$states];
        }

        if ($operator == 'and') {
            foreach ($states as $state) {
                $this->builder->whereHas('states', function ($q) use ($state) {
                    $q->where('code', $state);
                });
            }
        }

        if ($operator == 'or') {
            $this->builder->whereHas('states', function ($q) use ($states) {
                $q->whereIn('code', $states);
            });
        }

        return $this->builder->with('states');
    }

    /**
     * Check whether a nurse can call a patient.
     * checks licenced states, schedule, and nurse holidays.
     *
     * @param $patientUserId
     */
    public function canCallPatient($patientUserId)
    {
        $patient = User::with('patientInfo.contactWindows')
                       ->where('id', $patientUserId)
                       ->first();

        //check state
        $this->states($patient->state);

        $patientContactWindows = $patient->patientInfo->contactWindows->map(function ($window) {
            //check schedule
            $this->windows(function ($q) use ($window) {
                $q->orWhere([
                    ['day_of_week', '=', $window->day_of_week],
                    ['window_time_start', '<=', $window->window_time_start],
                    ['window_time_end', '>=', $window->window_time_end],
                ]);
            });

            //check if the nurse is on holiday
            $this->holidays(function ($q) use ($window) {
                $q->orWhere('date', '!=', carbonGetNext($window->day_of_week)->toDateString());
            });
        });
    }

    /**
     * Get the windows for each nurse
     *
     * @return Builder
     */
    public function windows($callBack = null)
    {
        if ($callBack) {
            $this->builder->whereHas('windows', $callBack);
        }

        return $this->builder->with('windows');
    }

    /**
     * Get the holidays for each nurse
     *
     * @return Builder
     */
    public function holidays($callBack = null)
    {
        if ($callBack) {
            $this->builder->whereHas('holidays', $callBack);
        }

        return $this->builder->with('holidays');
    }

    /**
     * Get the calls for each nurse
     *
     * @return Builder
     */
    public function calls($callBack = null)
    {
        if ($callBack) {
            $this->builder->whereHas('user.outboundCalls', $callBack);
        }

        return $this->builder->with('user.outboundCalls');
    }

    /**
     * Get the user model for a nurse
     *
     * @return Builder
     */
    public function user() {
        if ($this->request->has('compressed')) {
            return $this->builder->select([ 'id', 'user_id', 'status' ])->with(['user' => function ($q) {
                return $q->select([ 'id', 'display_name' ]);
            }])->with(['states' => function ($q) {
                return $q->select(['code']);
            }]);
        }
        return $this->builder->with('user');
    }


    /**
     * Search for a Nurse by full name.
     *
     * @param $term
     *
     * @return $this
     */
    public function search($term) {
        return $this->builder
            ->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%$term%");
            });
    }
}
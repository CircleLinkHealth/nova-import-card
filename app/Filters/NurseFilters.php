<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use CircleLinkHealth\Core\Filters\QueryFilters;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NurseFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
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
     * Get the calls for each nurse.
     *
     * @param mixed|null $callBack
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
     * Check whether a nurse can call a patient.
     * checks licenced states, schedule, and nurse holidays.
     *
     * @param $patientUserId
     */
    public function canCallPatient($patientUserId)
    {
        $user = User::with('patientInfo.contactWindows')
            ->where('id', $patientUserId)
            ->first();

        return $this->builder->whereHas('user', function ($q) use ($user) {
            return $q->ofPractice($user->program_id);
        });
    }

    public function globalFilters(): array
    {
        return [
            'status' => 'active',
            'user'   => true,
        ];
    }

    /**
     * Get the holidays for each nurse.
     *
     * @param mixed|null $callBack
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
     * Search for a Nurse by full name.
     *
     * @param $term
     *
     * @return $this
     */
    public function search($term)
    {
        return $this->builder
            ->whereHas('user', function ($q) use ($term) {
                $q->where('display_name', 'like', "%${term}%");
            });
    }

    /**
     * Get the states the nurse is licenced in.
     * By default the and operator is selected, which means that only nurses that include all states will be included.
     *
     * @param string $states   Comma delimited State Codes. Example: 'NJ, NY, GA'
     * @param string $operator Can 'and' or 'or'
     *
     * @return Builder
     */
    public function states($states = null, $operator = 'and')
    {
        if ( ! $states) {
            return $this->builder->with('states');
        }

        if (Str::contains($states, ',')) {
            $states = explode(',', $states);
        }

        if ( ! is_array($states)) {
            $states = [$states];
        }

        if ('and' == $operator) {
            foreach ($states as $state) {
                $this->builder->whereHas('states', function ($q) use ($state) {
                    $q->where('code', $state);
                });
            }
        }

        if ('or' == $operator) {
            $this->builder->whereHas('states', function ($q) use ($states) {
                $q->whereIn('code', $states);
            });
        }

        return $this->builder->with('states');
    }

    /**
     * Get nurses that are licenced in any of the states provided.
     *
     * @param string $states   Comma delimited State Codes. Example: 'NJ, NY, GA'
     * @param string $operator Can 'and' or 'or'
     *
     * @return Builder
     */
    public function statesOr($states = null, $operator = 'or')
    {
        return $this->states($states, $operator);
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
     * Get the user model for a nurse.
     *
     * @return Builder
     */
    public function user()
    {
        if ($this->request->has('compressed')) {
            return $this->builder->select(['id', 'user_id', 'status'])
                ->with([
                    'user' => function ($q) {
                        return $q->select(['id', 'display_name', 'program_id']);
                    },
                    'states' => function ($q) {
                        return $q->select(['code']);
                    },
                    'user.roles' => function ($q) {
                        return $q->select(['name']);
                    },
                    'user.practices' => function ($q) {
                        return $q->select(['id']);
                    },
                ]);
        }

        //we need to send roles down to client to differentiate in-house nurses from clh nurses
        if ($this->request->has('canCallPatient')) {
            $practiceId = User::find($this->request->get('canCallPatient'))->primaryPractice->id;

            return $this->builder->with([
                // 'user', //implied
                'user.roles' => function ($q) use ($practiceId) {
                    return $q->where('program_id', '=', $practiceId)->select(['name']);
                },
            ]);
        }

        return $this->builder->with('user');
    }

    /**
     * Get the windows for each nurse.
     *
     * @param mixed|null $callBack
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
}

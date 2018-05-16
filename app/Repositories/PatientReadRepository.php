<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/10/2018
 * Time: 8:59 PM
 */

namespace App\Repositories;


use App\User;
use App\PatientSearchModel;
use App\Filters\PatientFilters;

class PatientReadRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user::ofType('participant')
                           ->with('patientInfo');
    }

    public function model()
    {
        return $this->user;
    }

    public function patients(PatientFilters $filters) {
        $users = $this->model()->filter($filters)->ofType('participant')->whereHas('patientInfo');
        if (!$filters->isExcel()) { //check that an excel file is not requested]
            if (isset($filters->filters()['rows']) && $filters->filters()['rows'] == 'all') {
                $users = $users->paginate($users->count());
            }
            else {
                $users = $users->paginate($filters->filters()['rows'] ?? 15);
            }
        }
        else {
            if (isset($filters->filters()['rows']) && is_integer((int)$filters->filters()['rows'])) {
                $users = $users->paginate($filters->filters()['rows']);
            }
            else {
                $users = $users->paginate($users->count());
            }
        }
        $users->getCollection()->transform(function ($user) use ($filters) {
            if ($filters->isAutocomplete()) {
                $user = optional($user)->autocomplete();
            }
            else {
                $user = optional($user)->safe();
            }
            return $user;
        });
        return $users;
    }

    /**
     * Scope for paused patients
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function paused()
    {
        $this->user
            ->whereHas('patientInfo', function ($q) {
                $q->ccmStatus('paused');
            });

        return $this;
    }

    /**
     * Scope for patients whose paused letter was not printed yet
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function pausedLetterNotPrinted()
    {
        $this->user
            ->whereHas('patientInfo', function ($q) {
                $q->whereNull('paused_letter_printed_at');
            });

        return $this;
    }

    public function fetch($resetQuery = true)
    {
        $result = $this->user->get();

        if ($resetQuery) {
            $this->user = User::ofType('participant')
                              ->with('patientInfo');
        }

        return $result;
    }

    public function search(PatientSearchModel $searchModel) {
        return $searchModel->results();
    }
}
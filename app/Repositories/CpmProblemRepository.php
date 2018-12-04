<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmProblem;

class CpmProblemRepository
{
    public function model()
    {
        return app(CpmProblem::class);
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function noDiabetesFilter()
    {
        return $this->model()->where('name', '!=', 'Diabetes');
    }
}

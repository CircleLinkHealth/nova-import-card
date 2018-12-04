<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class VaseCriteria.
 */
class CriteriaFactory implements CriteriaInterface
{
    private $handler;

    public function __construct($fnHandler)
    {
        $this->handler = $fnHandler;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return call_user_func($this->handler, $model);
    }
}

<?php
namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class VaseCriteria
 * @package App\Repositories\Criteria
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
        $model = call_user_func($this->handler, $model);
        return $model;
    }
}

<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */
namespace CircleLinkHealth\Core\Facades;

class QueryBuilder extends \Illuminate\Database\Query\Builder
{
    public function get($columns = ['*']) {

        //WIP
//        $table = $this->from;
//        $class =  getModelFromTable('patient_info');
//        $model = with(new $class);
//        $phiFields = $model->phi;
        //check for PHI in query

        return parent::get($columns);

    }

    function getModelFromTable($table)
    {
        foreach( get_declared_classes() as $class ) {
            if( is_subclass_of( $class, 'Illuminate\Database\Eloquent\Model' ) ) {
                $model = new $class;
                if ($model->getTable() === $table)
                    return $class;
            }
        }
        return false;
    }
}
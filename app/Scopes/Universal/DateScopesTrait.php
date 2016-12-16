<?php namespace App\Scopes\Universal;

use Carbon\Carbon;

/**
 * Class DateScopesTrait
 *
 * Applies to Models.
 * Put all date specific scopes that we can use with multiple Models here.
 */
trait DateScopesTrait
{
    /**
     * Scope a query to only include activities created today. Defaults to created_at field, but a different field may
     * be specified.
     *
     * @param $builder
     * @param string $field
     */
    public function scopeCreatedToday(
        $builder,
        $field = 'created_at'
    ) {
        $builder->where(function ($q) use
        (
            $field
        ) {
            $q->where($field, '>=', Carbon::now()->startOfDay())
                ->where($field, '<=', Carbon::now()->endOfDay());
        });
    }


    /**
     * Scope a query to only include activities created this month. Defaults to created_at field, but a different field
     * may be specified.
     *
     * @param $builder
     * @param string $field
     */
    public function scopeCreatedThisMonth(
        $builder,
        $field = 'created_at'
    ) {
        $builder->where(function ($q) use
        (
            $field
        ) {
            $q->where($field, '>=', Carbon::now()->startOfMonth())
                ->where($field, '<=', Carbon::now()->endOfMonth());
        });
    }
}
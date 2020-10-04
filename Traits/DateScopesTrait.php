<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Traits;

use Carbon\Carbon;

/**
 * Class DateScopesTrait.
 *
 * Applies to Models.
 * Put all date specific scopes that we can use with multiple Models here.
 */
trait DateScopesTrait
{
    /**
     * Scope a query to only include activities created in the month given month. Defaults to created_at field, but a different field
     * may be specified.
     *
     * @param $builder
     * @param string $field
     */
    public function scopeCreatedInMonth(
        $builder,
        Carbon $date,
        $field = 'created_at'
    ) {
        $builder->where(function ($q) use (
            $field, $date
        ) {
            $q->where($field, '>=', $date->copy()->startOfMonth()->toDateTimeString())
                ->where($field, '<=', $date->copy()->endOfMonth()->toDateTimeString());
        });
    }

    /**
     * Scope a query to only include activities created on a day. Defaults to created_at field, but a different field may
     * be specified.
     *
     * @param $builder
     * @param string $field
     */
    public function scopeCreatedOn(
        $builder,
        Carbon $date,
        $field = 'created_at'
    ) {
        $builder->where(function ($q) use (
            $field, $date
        ) {
            $q->where($field, '>=', $date->copy()->startOfDay()->toDateTimeString())
                ->where($field, '<=', $date->copy()->endOfDay()->toDateTimeString());
        });
    }

    /**
     * Wrapper for createdOn Scope for null dates.
     *
     * @param $builder
     * @param Carbon $date
     * @param string $field
     */
    public function scopeCreatedOnIfNotNull(
        $builder,
        Carbon $date = null,
        $field = 'created_at'
    ) {
        $builder->when( ! is_null($date), function ($sq) use (
            $date, $field
        ) {
            $sq->createdOn($date, $field);
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
        $builder->createdInMonth(Carbon::now(), $field);
    }

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
        $builder->where(function ($q) use (
            $field
        ) {
            $q->where($field, '>=', Carbon::now()->startOfDay()->toDateTimeString())
                ->where($field, '<=', Carbon::now()->endOfDay()->toDateTimeString());
        });
    }

    /**
     * Scope a query to only include activities created yesterday. Defaults to created_at field, but a different field may
     * be specified.
     *
     * @param $builder
     * @param string $field
     */
    public function scopeCreatedYesterday(
        $builder,
        $field = 'created_at'
    ) {
        $builder->where(function ($q) use (
            $field
        ) {
            $q->where($field, '>=', Carbon::yesterday()->startOfDay()->toDateTimeString())
                ->where($field, '<=', Carbon::yesterday()->endOfDay()->toDateTimeString());
        });
    }
}

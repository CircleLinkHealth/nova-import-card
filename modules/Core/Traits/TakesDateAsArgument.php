<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Carbon\Carbon;

trait TakesDateAsArgument
{
    public function getDateAsCarbon(string $argumentName, string $format = 'Y-m-d', bool $isOptional = true): ?Carbon
    {
        $dateString = $this->argument($argumentName);

        if ( ! $isOptional) {
            return Carbon::createFromFormat($format, $this->argument($argumentName, $dateString));
        }

        return empty($dateString)
            ? Carbon::now()
            : Carbon::createFromFormat($format, $this->argument($argumentName, $dateString));
    }

    public function getMonthAsCarbon(string $argumentName, string $format = 'Y-m-d', bool $isOptional = true): ?Carbon
    {
        $dateString = $this->argument($argumentName);

        if ( ! $isOptional) {
            return Carbon::createFromFormat($format, $this->argument($argumentName, $dateString));
        }

        return empty($dateString)
            ? Carbon::now()->startOfMonth()
            : Carbon::createFromFormat($format, $this->argument($argumentName, $dateString))->startOfMonth();
    }
}

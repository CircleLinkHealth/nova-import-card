<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Contracts;

use CircleLinkHealth\SharedModels\HasProblemCodes;
use Illuminate\Support\Collection;

/**
 * A condition a Patient has (Diabetes, Hypertension, etc)
 * Most of these methods are satisfied be CircleLinkHealth\SharedModels\HasProblemCodes.
 *
 * @see HasProblemCodes
 *
 * Interface Problem
 */
interface Problem
{
    /**
     * Returns a Collection of key - value pairs of code type and code.
     * eg. ['snomed_code' => '123', 'icd_9_code' => '657'].
     *
     * @return Collection
     */
    public function codeMap();

    /**
     * A HasMany relationship between the Problem and Codes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes();

    /**
     * Queries codes() for icd10Codes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function icd10Codes();

    /**
     * Queries codes() for icd9Codes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function icd9Codes();

    /**
     * Queries codes() for snomedCodes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function snomedCodes();
}

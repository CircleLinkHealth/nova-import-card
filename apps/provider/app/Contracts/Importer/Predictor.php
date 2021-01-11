<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Importer;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 5:34 PM.
 */
interface Predictor
{
    /**
     * Predicts the Location, and Practice for a medical record.
     * Returns an id.
     *
     * @return int
     */
    public function predict();
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Predictors;

use App\Contracts\Importer\Predictor;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 5:36 PM.
 */
class HistoricPracticePredictor extends BaseHistoricPredictor implements Predictor
{
    /**
     * Predicts the Practice for a medical record.
     * Returns a Practice id.
     *
     * @return int
     */
    public function predict()
    {
        $custodianPredictions = $this->custodianLookup('practice_id');
        $providersPredictions = $this->providersLookup('practice_id', 5);
        $addressesPredictions = $this->addressesLookup('practice_id', 2);

        return $this->makePrediction(
            'practice_id',
            $addressesPredictions,
            $custodianPredictions,
            $providersPredictions
        );
    }
}

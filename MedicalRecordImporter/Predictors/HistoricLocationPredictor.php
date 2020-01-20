<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Predictors;

use App\Contracts\Importer\Predictor;
use App\Importer\Predictors\BaseHistoricPredictor;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 5:36 PM.
 */
class HistoricLocationPredictor extends BaseHistoricPredictor implements Predictor
{
    /**
     * Predicts the Location for a medical record.
     * Returns a Location id.
     *
     * @return int
     */
    public function predict()
    {
        $custodianPredictions = $this->custodianLookup('location_id');
        $providersPredictions = $this->providersLookup('location_id', 5);
        $addressesPredictions = $this->addressesLookup('location_id', 3);

        return $this->makePrediction(
            'location_id',
            $addressesPredictions,
            $custodianPredictions,
            $providersPredictions
        );
    }
}

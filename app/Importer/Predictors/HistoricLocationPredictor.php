<?php namespace App\Importer\Predictors;

use App\Contracts\Importer\Predictor;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 5:36 PM
 */
class HistoricLocationPredictor extends BaseHistoricPredictor implements Predictor
{
    /**
     * Predicts the Location, and Practice for a medical record.
     * Returns an id.
     *
     * @return integer
     */
    public function predict()
    {
        $custodianPredictions = $this->custodianLookup('location_id');
        $providersPredictions = $this->providersLookup('location_id', 5);
        $addressesPredictions = $this->addressesLookup('location_id', 3);

        return $this->makePrediction('location_id', $addressesPredictions, $custodianPredictions,
            $providersPredictions);
    }
}
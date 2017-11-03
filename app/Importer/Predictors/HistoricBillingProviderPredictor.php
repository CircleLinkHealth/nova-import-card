<?php namespace App\Importer\Predictors;

use App\Contracts\Importer\Predictor;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 5:36 PM
 */
class HistoricBillingProviderPredictor extends BaseHistoricPredictor implements Predictor
{
    /**
     * Predicts the Billing Provider for a medical record.
     * Returns a Billing Provider id.
     *
     * @return integer
     */
    public function predict()
    {
        $custodianPredictions = $this->custodianLookup('billing_provider_id');
        $providersPredictions = $this->providersLookup('billing_provider_id', 50);
        $addressesPredictions = $this->addressesLookup('billing_provider_id', 2);

        return $this->makePrediction(
            'billing_provider_id',
            $addressesPredictions,
            $custodianPredictions,
            $providersPredictions
        );
    }
}

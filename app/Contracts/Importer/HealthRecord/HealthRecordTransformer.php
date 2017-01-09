<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 2:23 AM
 */

namespace App\Contracts\Importer\HealthRecord;


use App\CLH\CCD\ItemLogger\CcdAllergyLog;
use App\CLH\CCD\ItemLogger\CcdDemographicsLog;
use App\CLH\CCD\ItemLogger\CcdDocumentLog;
use App\CLH\CCD\ItemLogger\CcdMedicationLog;
use App\CLH\CCD\ItemLogger\CcdProblemLog;
use App\CLH\CCD\ItemLogger\CcdProviderLog;

interface HealthRecordTransformer
{
    /**
     * Transform the Allergies Section into a Log model.
     *
     * @return CcdAllergyLog
     */
    public function transformAllergiesSection() : CcdAllergyLog;

    /**
     * Transform the Demographics Section into a Log model.
     *
     * @return CcdDemographicsLog
     */
    public function transformDemographicsSection() : CcdDemographicsLog;

    /**
     * Transform the Document Section into a Log model.
     *
     * @return CcdDocumentLog
     */
    public function transformDocumentSection() : CcdDocumentLog;

    /**
     * Transform the Medications Section into a Log model.
     *
     * @return CcdMedicationLog
     */
    public function transformMedicationsSection() : CcdMedicationLog;

    /**
     * Transform the Problems Section into a Log model.
     *
     * @return CcdProblemLog
     */
    public function transformProblemsSection() : CcdProblemLog;

    /**
     * Transform the Providers Section into a Log model.
     *
     * @return CcdProviderLog
     */
    public function transformProvidersSection() : CcdProviderLog;
}
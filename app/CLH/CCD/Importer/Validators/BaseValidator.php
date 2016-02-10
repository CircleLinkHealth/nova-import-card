<?php

namespace App\CLH\CCD\Importer\Validators;

use App\CLH\Contracts\CCD\Parser;
use App\CLH\Contracts\DataTemplate;
use App\ParsedCCD;

abstract class BaseValidator
{
    protected $blogId;
    protected $ccd;
    protected $meta;
    protected $parsedCcdObj;
    protected $routine;
    protected $userId;

    public function __construct ($blogId, ParsedCCD $ccd, DataTemplate $meta = null)
    {
        $this->blogId = $blogId;
        $this->ccd = json_decode($ccd->ccd);
        $this->meta = $meta;
        $this->parsedCcdObj = $ccd;
        $this->userId = $ccd->user_id;
        $this->routine = self::getRoutine();
    }

    /**
     * The EHRs listed below do not fill out the end end date, or status for medications.
     * Medications that DO have a start date, but DO NOT HAVE an end date will be considered active.
     * We are setting the $importIfEndDateIsNull flag to point out those EHRs, and then we check if
     * the HAVE a start date but DO NOT HAVE and end date.
     */
    protected function importIfEndDateIsNullAndStartDateExists()
    {
        if (empty($this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root)) return false;

        $ehrId = $this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root;

        return in_array($ehrId, [
            '2.16.840.1.113883.3.929', // STI
        ]);
    }

    /**
     * Check if there is a special rules set defined for this provider or EHR,
     * otherwise return the standard ruleset.
     *
     * @return array
     */
    protected function getRoutine()
    {
        return self::getRuleSet()
            ? self::getRuleSet()
            : [
                //Medications Import
                    //How to import?
                    'importAllMeds' => false,
                    'importMedsUsingDates' => true,
                    'importMedsUsingStatus' => false,

                    //What to import?
                    'importReferenceMedTitleAndSig' => false,
                    'importProductMedNameAndText' => true
            ];
    }

    protected function getRuleSet()
    {
        if ($this->ccd->document->author->name->family == 'Mazhar')
        {
            return [
                'importAllMeds' => true,

                /**
                 * Import reference_title and reference_sig from the HTML part of the CCD, using the reference ID.
                 */
                'importReferenceMedTitleAndSig' => true,
                /**
                 * Import name and text from the json parsed ccd.
                 * This is the standard way
                 */
                'importProductMedNameAndText' => false
            ];
        }

        return false;
    }
}
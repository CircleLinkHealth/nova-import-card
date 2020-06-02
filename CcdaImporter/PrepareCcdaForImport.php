<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Eligibility\CcdaImporter;

use App\Search\ProviderByName;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporter;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\MedicalRecord\MedicalRecordFactory;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class PrepareCcdaForImport
{
    const TEMP_VAR_COMMONWEALTH_PRACTICE_NAME = 'commonwealth-pain-associates-pllc';

    /**
     * @var Ccda
     */
    private $ccda;
    /**
     * @var Enrollee|null
     */
    private $enrollee;

    /**
     * PrepareCcdaForImport constructor.
     */
    public function __construct(Ccda $ccda, Enrollee $enrollee = null)
    {
        $this->ccda     = $ccda;
        $this->enrollee = $enrollee;
    }

    /**
     * This is a solution for commonwealth importing!
     * Likely to change.
     *
     * If there is a specific template for this practice, decorate the ccda.json.
     *
     *
     * @return \CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord|null
     */
    public static function attemptToDecorateCcda(User $user, Ccda $ccda)
    {
        if (self::TEMP_VAR_COMMONWEALTH_PRACTICE_NAME !== optional($user->primaryPractice)->name) {
            return $ccda;
        }

        if ($mr = MedicalRecordFactory::create($user, $ccda)) {
            if ( ! empty($mr)) {
                $ccda->json = $mr->toJson();
                $ccda->bluebuttonJson(true);
                $ccda->save();
            }
        }

        return $ccda;
    }

    public function fillInSupplementaryData()
    {
        if ($this->ccda->practice_id && $this->ccda->location_id && $this->ccda->billing_provider_id) {
            return $this;
        }
        $supp = SupplementalPatientData::forPatient($this->ccda->practice_id, $this->ccda->patient_first_name, $this->ccda->patient_last_name, $this->ccda->patientDob());

        if ( ! $supp) {
            return $this;
        }

        if ( ! $this->ccda->location_id) {
            $this->ccda->location_id = $supp->location_id;
        }

        if ( ! $this->ccda->billing_provider_id) {
            $this->ccda->billing_provider_id = $supp->billing_provider_user_id;
        }

        return $this;
    }

    public function fillLocationFromAthenaDepartmentId()
    {
        if ($this->ccda->location_id) {
            return $this;
        }

        if ( ! $this->ccda->practice_id) {
            return $this;
        }

        if ( ! Practice::where('id', $this->ccda->practice_id)->whereHas('ehr', function ($q) {
            $q->where('name', Ehr::ATHENA_EHR_NAME);
        })->exists()) {
            return $this;
        }

        $deptId = optional($this->ccda->targetPatient)->ehr_department_id;

        if ( ! $deptId) {
            $this->ccda->queryForOtherCcdasForTheSamePatient()->with('targetPatient')->chunkById(10, function ($otherCcdas) use (&$deptId) {
                foreach ($otherCcdas as $otherCcda) {
                    $targetPatient = $otherCcda->targetPatient;

                    if ($targetPatient instanceof TargetPatient && is_numeric($targetPatient->ehr_department_id)) {
                        $deptId = $targetPatient->ehr_department_id;
                        //break chunking
                        return false;
                    }
                }
            });
        }

        if ($deptId) {
            $this->ccda->location_id = \Cache::remember("cpm_practice_{$this->ccda->practice_id}___location_for_athena_department_id_$deptId", 2, function () use ($deptId) {
                return Location::where('practice_id', $this->ccda->practice_id)->where('external_department_id', $deptId)->value('id');
            });
        }

        return $this;
    }

    /**
     * Attempt to fill in Practice, Location, and Billing Provider on this CCDA.
     *
     * @return $this|MedicalRecord
     */
    public function guessPracticeLocationProvider()
    {
        if ($this->ccda->practice_id && $this->ccda->location_id && $this->ccda->billing_provider_id) {
            return $this;
        }

        $this->ccda->loadMissing(['billingProvider', 'targetPatient']);

        //We assume Athena API has the most up-to-date and reliable date
        //so we look there first
        $this->fillLocationFromAthenaDepartmentId();

        //Second most reliable place is ccdas.referring_provider_name.
        if ( ! $this->ccda->billingProvider && $term = $this->ccda->getReferringProviderName()) {
            $this->ccda->billingProvider = self::searchBillingProvider($term, $this->ccda->practice_id);
        }

        if ($this->ccda->billingProvider instanceof User) {
            $this->setAllPracticeInfoFromProvider($this->ccda->billingProvider);
        }

        if ($this->ccda->location_id) {
            return $this;
        }

        //Check if we have any locations whose address line 1 matches that in documentation of
        $this->setLocationFromDocumentationOfAddressInCcda($this->ccda);

        if ($this->ccda->location_id) {
            return $this;
        }

        //As a last result check if we have other ccdas
        $this->ccda->queryForOtherCcdasForTheSamePatient()->chunkById(5, function ($otherCcdas) use (&$deptId) {
            foreach ($otherCcdas as $otherCcda) {
                $this->setLocationFromDocumentationOfAddressInCcda($otherCcda);

                if ($this->ccda->location_id) {
                    return false;
                }
            }
        });

        return $this;
    }

    public function handle()
    {
        $this->fillInSupplementaryData()
            ->guessPracticeLocationProvider();

        if ( ! $this->ccda->json) {
            $this->ccda->bluebuttonJson();
        }

        if ( ! $this->ccda->patient_mrn) {
            //fetch a fresh instance from the DB to have virtual fields
            $this->ccda = $this->ccda->fresh();
        }

        $patient = $this->ccda->load('patient')->patient ?? null;

        // If this is a survey only patient who has not yet enrolled, we should not enroll them.
        if (self::isUnenrolledSurveyUser($patient, $this->enrollee)) {
            return $this;
        }

        if ($patient) {
            $this->ccda = self::attemptToDecorateCcda($patient, $this->ccda);
        }

        $this->ccda = with(new CcdaImporter($this->ccda, $patient, $this->enrollee))->attemptImport();

        if ($this->ccda->isDirty()) {
            $this->ccda->save();
        }

        $this->ccda->queryForOtherCcdasForTheSamePatient()->update([
            'mrn'                     => $this->ccda->mrn,
            'referring_provider_name' => $this->ccda->referring_provider_name,
            'location_id'             => $this->ccda->location_id,
            'practice_id'             => $this->ccda->practice_id,
            'billing_provider_id'     => $this->ccda->billing_provider_id,
            'patient_id'              => $this->ccda->patient_id,
            'status'                  => $this->ccda->status,
            'validation_checks'       => null,
        ]);

        event(new CcdaImported($this->ccda->getId()));

        return $this->ccda;
    }

    /**
     * Search for a Billing Provider using a search term, and.
     *
     * @param string $term
     * @param int    $practiceId
     */
    public static function searchBillingProvider(string $term = null, int $practiceId = null): ?User
    {
        if ( ! $practiceId) {
            return null;
        }
        if ( ! $term) {
            return null;
        }
        $baseQuery = (new ProviderByName())->query($term);

        if ('algolia' === config('scout.driver')) {
            return $baseQuery
                ->with(
                    [
                        'typoTolerance' => true,
                    ]
                )->when(
                    ! empty($practiceId),
                    function ($q) use ($practiceId) {
                        $q->whereIn('practice_ids', [$practiceId]);
                    }
                )
                ->first();
        }

        return $baseQuery->when(
            ! empty($practiceId),
            function ($q) use ($practiceId) {
                if ( ! method_exists($q, 'ofPractice')) {
                    return $q->whereIn('practice_ids', [$practiceId]);
                }
                $q->ofPractice($practiceId);
            }
        )->first();
    }

    /**
     * If this is a survey only patient who has not yet enrolled, we should not enroll them.
     */
    private static function isUnenrolledSurveyUser(?User $patient, ?Enrollee $enrollee): bool
    {
        if (is_null($patient)) {
            return false;
        }

        if ( ! $patient->isSurveyOnly()) {
            return false;
        }

        if (is_null($enrollee)) {
            return false;
        }

        if (Enrollee::ENROLLED === $enrollee->status) {
            return false;
        }

        return true;
    }

    private function setAllPracticeInfoFromProvider(User $provider)
    {
        if ( ! $this->ccda->practice_id) {
            $this->ccda->setPracticeId($provider->program_id);
        }

        $this->ccda->setBillingProviderId($provider->id);

        if ($this->ccda->location_id) {
            return;
        }

        if (1 === count($provider->locations)) {
            $this->ccda->setLocationId($provider->locations->first()->id);
        }

        if ($this->ccda->location_id) {
            return;
        }

        if ($providerAddress = $this->ccda->bluebuttonJson()->demographics->provider->address->street[0] ?? null) {
            $locations = $provider->locations->where('address_line_1', $providerAddress);

            if (1 === $locations->count()) {
                $this->ccda->setLocationId($locations->first()->id);
            }
        }
    }

    private function setLocationFromDocumentationOfAddressInCcda(Ccda $ccda)
    {
        //Get address line 1 from documentation_of section of ccda
        $addresses = collect($ccda->bluebuttonJson()->document->documentation_of)->pluck('address.street')->filter(function ($address) {
            if (empty($address[0] ?? null)) {
                return null;
            }

            return $address[0];
        });

        //only do this if there's a just one address in the CCDA.
        //we don't wanna take a guess on what the actual patient's location may be
        if (1 === $addresses->count()) {
            if ($location = Location::where('address_line_1', $addresses->last())->where('practice_id', $this->ccda->practice_id)->first()) {
                $this->ccda->setLocationId($location->id);
            }
        }
    }
}

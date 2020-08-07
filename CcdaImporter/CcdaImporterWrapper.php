<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Eligibility\CcdaImporter;

use App\Search\LocationByName;
use App\Search\ProviderByName;
use CircleLinkHealth\Customer\Console\Commands\CreateLocationsFromAthenaApi;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporter;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\MedicalRecord\MedicalRecordFactory;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CcdaImporterWrapper
{
    /**
     * @var Ccda
     */
    private $ccda;
    /**
     * @var Enrollee|null
     */
    private $enrollee;

    /**
     * CcdaImporterWrapper constructor.
     */
    public function __construct(Ccda $ccda, Enrollee &$enrollee = null)
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
     * @return \CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord|null
     */
    public static function attemptToDecorateCcda(User $user, Ccda $ccda)
    {
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

        if ( ! self::isAthenaPractice($this->ccda->practice_id)) {
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

            if ( ! $this->ccda->location_id && $ehrPracticeId = optional($this->ccda->targetPatient)->ehr_practice_id) {
                $aLoc = \Cache::remember("paid_api_pull:athena_ehrPracticeId_departments_{$ehrPracticeId}", 60, function () use ($ehrPracticeId) {
                    return app(AthenaApiImplementation::class)->getDepartments($ehrPracticeId);
                })->where('departmentid', $deptId)->first();

                if ($aLoc) {
                    $cpmLocation = CreateLocationsFromAthenaApi::createNewLocationFromAthenaApiDeprtment($aLoc, $this->ccda->practice_id);
                }
            }
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

        $this->ccda->loadMissing(['billingProvider', 'targetPatient', 'directMessage.senderDmAddress' => function ($q) {
            $q->has('users')->with('users');
        }]);

        //We assume Athena API has the most up-to-date and reliable date
        //so we look there first
        $this->fillLocationFromAthenaDepartmentId();

        $provider = $this->ccda->billingProvider;

        //Try this before algolia to save costs
        if ( ! $provider && $this->ccda->directMessage && $this->ccda->directMessage->senderDmAddress && $this->ccda->directMessage->senderDmAddress->users) {
            $provider = $this->ccda->directMessage->senderDmAddress->users->first() ?? null;
        }

        //Second most reliable place is ccdas.referring_provider_name.
        if ( ! $provider && $term = trim($this->ccda->getReferringProviderName())) {
            $provider = self::searchBillingProvider($term, $this->ccda->practice_id);
        }

        if ( ! $provider && $this->ccda->bluebuttonJson()->document->author->name && $name = $this->ccda->bluebuttonJson()->document->author->name->given) {
            $provider = $name[0].' '.$this->ccda->bluebuttonJson()->document->author->name->family;
        }

        if ($provider instanceof User) {
            $this->setAllPracticeInfoFromProvider($provider);
        }

        if ($this->ccda->location_id) {
            return $this;
        }

        //Check if we have any locations whose address line 1 matches that in documentation of
        $this->setLocationFromDocumentationOfAddressInCcda($this->ccda);

        if ($this->ccda->location_id) {
            return $this;
        }

        $this->setLocationFromEncountersInCcda($this->ccda);

        if ($this->ccda->location_id) {
            return $this;
        }

        $this->setLocationFromAuthorAddressInCcda($this->ccda);

        if ($this->ccda->location_id) {
            return $this;
        }

        if ($this->enrollee) {
            $this->setLocationFromEnrolleeFacility($this->ccda, $this->enrollee);
        }

        if ($this->ccda->location_id) {
            return $this;
        }

        $this->setLocationFromDocumentLocationName();

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

                $this->setLocationFromEncountersInCcda($otherCcda);

                if ($this->ccda->location_id) {
                    return false;
                }

                $this->setLocationFromAuthorAddressInCcda($otherCcda);

                if ($this->ccda->location_id) {
                    return $this;
                }
            }
        });

        return $this;
    }

    /**
     * Wraps CcdaImporter@attemptImport.
     * Performs some pre and post import steps.
     *
     * @see CcdaImporter
     *
     * @throws \Throwable
     *
     * @return $this|Ccda
     */
    public function import()
    {
        $patient = $this->ccda->load('patient')->patient ?? null;

        // If this is a survey only patient who has not yet enrolled, we should not enroll them.
        if (self::isUnenrolledSurveyUser($patient, $this->enrollee)) {
            return $this;
        }

        if ($patient) {
            $this->ccda = self::attemptToDecorateCcda($patient, $this->ccda);
        }

        if ( ! $this->ccda->json) {
            $this->ccda->bluebuttonJson();
        }

        if ( ! $this->ccda->patient_mrn) {
            //fetch a fresh instance from the DB to have virtual fields
            $this->ccda = $this->ccda->fresh();
        }

        $this->fillInSupplementaryData()
            ->guessPracticeLocationProvider();

        $this->ccda = with(new CcdaImporter($this->ccda, $this->enrollee))->attemptImport();

        if ($this->ccda->isDirty()) {
            $this->ccda->save();
        }

        $updated = $this->ccda->queryForOtherCcdasForTheSamePatient()->update([
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

    public static function isAthenaPractice(int $practiceId): bool
    {
        return Cache::remember("is_athena_ehr_practice_id_$practiceId", 2, function () use ($practiceId) {
            return Practice::where('id', $practiceId)->whereHas('ehr', function ($q) {
                $q->where('name', Ehr::ATHENA_EHR_NAME);
            })->exists();
        });
    }

    /**
     * Search for a Billing Provider using a search term.
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
        if ($provider = self::mysqlMatchProvider($term, $practiceId)) {
            return $provider;
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
     * Search for a Location using a search term.
     *
     * @param string $term
     * @param int    $practiceId
     */
    public static function searchLocation(string $term = null, int $practiceId = null): ?Location
    {
        if ( ! $practiceId) {
            return null;
        }
        if ( ! $term) {
            return null;
        }
        if ($location = self::mysqlMatchLocation($term, $practiceId)) {
            return $location;
        }
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

        if (in_array($enrollee->status, [Enrollee::ENROLLED, Enrollee::CONSENTED])) {
            return false;
        }

        return true;
    }

    private static function mysqlMatchLocation(string $term, int $practiceId): ?Location
    {
        $term = self::prepareForMysqlMatch($term);

        return Location::whereRaw("MATCH(name) AGAINST('$term')")->where('practice_id', $practiceId)->first();
    }

    private static function mysqlMatchProvider(string $term, int $practiceId): ?User
    {
        $term = self::prepareForMysqlMatch($term);

        return User::whereRaw("MATCH(display_name, first_name, last_name) AGAINST('$term')")->ofPractice($practiceId)->ofType('provider')->first();
    }

    private static function prepareForMysqlMatch(string $term)
    {
        return collect(explode(' ', $term))->transform(function ($term) {
            return "+$term";
        })->implode(' ');
    }

    private function replaceCommonAddressVariations($providerAddress)
    {
        if (Str::contains($providerAddress = strtolower($providerAddress), 'road')) {
            $providerAddress = str_replace('road', 'rd', $providerAddress);
        }

        if (Str::contains($providerAddress = strtolower($providerAddress), 'avenue')) {
            $providerAddress = str_replace('avenue', 'ave', $providerAddress);
        }

        return $providerAddress;
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
            $locations = $provider->locations->whereIn('address_line_1', [$providerAddress, $this->replaceCommonAddressVariations($providerAddress)]);

            if (1 === $locations->count()) {
                $this->ccda->setLocationId($locations->first()->id);
            }
        }
    }

    private function setLocationFromAuthorAddressInCcda(Ccda $ccda)
    {
        //Get address line 1 from documentation_of section of ccda
        $address = ((array) $ccda->bluebuttonJson()->document->author->address)['street'][0] ?? null;

        if (empty($address)) {
            return;
        }

        if ($location = Location::whereIn('address_line_1', [$address, $this->replaceCommonAddressVariations($address)])->where('practice_id', $this->ccda->practice_id)->first()) {
            $this->ccda->setLocationId($location->id);
        }
    }

    private function setLocationFromDocumentationOfAddressInCcda(Ccda $ccda)
    {
        //Get address line 1 from documentation_of section of ccda
        $addresses = collect($ccda->bluebuttonJson()->document->documentation_of)->map(function ($address) {
            $address = ((array) $address->address)['street'] ?? null;

            if (empty($address[0] ?? null)) {
                return null;
            }

            return $address[0];
        })->filter()->unique();

        //only do this if there's a just one address in the CCDA.
        //we don't wanna take a guess on what the actual patient's location may be
        if (1 === $addresses->count()) {
            $address = $addresses->last();
            if ($location = Location::whereIn('address_line_1', [$address, $this->replaceCommonAddressVariations($address)])->where('practice_id', $this->ccda->practice_id)->first()) {
                $this->ccda->setLocationId($location->id);
            }
        }
    }

    private function setLocationFromDocumentLocationName()
    {
        if ( ! empty($this->ccda->practice_id) && $locationName = $this->ccda->bluebuttonJson()->document->location->name) {
            return Location::where('name', $locationName)->where('practice_id', $this->ccda->practice_id)->value('id');
        }

        return null;
    }

    private function setLocationFromEncountersInCcda(Ccda $ccda)
    {
        //Get address line 1 from documentation_of section of ccda
        $addresses = collect(optional($ccda->bluebuttonJson())->encounters ?? [])->map(function ($encounter) {
            $encounter = (array) $encounter;
            $location = $encounter['address'] ?? [];

            if (empty($location)) {
                $location = $encounter['location'] ?? [];
            }

            if (empty($location)) {
                return null;
            }

            if ( ! is_array($location)) {
                $location = (array) $location;
            }

            $address = $location['street'] ?? null;

            if (empty($address[0] ?? null)) {
                return null;
            }

            return $address[0];
        })->filter()->unique();

        //only do this if there's a just one address in the CCDA.
        //we don't wanna take a guess on what the actual patient's location may be
        if (1 === $addresses->count()) {
            $address = $addresses->last();
            if ($location = Location::whereIn('address_line_1', [$address, $this->replaceCommonAddressVariations($address)])->where('practice_id', $this->ccda->practice_id)->first()) {
                $this->ccda->setLocationId($location->id);
            }
        }
    }

    private function setLocationFromEnrolleeFacility(Ccda &$ccda, Enrollee &$enrollee)
    {
        if ( ! $enrollee->facility_name) {
            return;
        }

        $location = Location::where('name', $enrollee->facility_name)->first();

        if ( ! $location) {
            $location = (new LocationByName())->query($enrollee->facility_name)->where('practice_id', $ccda->practice_id)->whereNotNull('practice_id')->first();
        }

        if ($location) {
            $ccda->location_id     = $location->id;
            $enrollee->location_id = $location->id;
        }
    }
}

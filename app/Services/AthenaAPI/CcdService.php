<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\Models\MedicalRecords\Ccda;
use CircleLinkHealth\Customer\Entities\Practice;

class CcdService
{
    /**
     * @var Calls
     */
    private $athenaApi;

    public function __construct(Calls $athenaApi)
    {
        $this->athenaApi = $athenaApi;
    }

    public function importCcds(array $patientIds, int $practiceId)
    {
        $imported = [];
        $practice = Practice::findOrFail($practiceId);

        $practiceId   = $practice->external_id;
        $departmentId = $practice->locations->first()->external_department_id;

        foreach ($patientIds as $id) {
            $id           = trim($id);
            $ccdaExternal = $this->athenaApi->getCcd($id, $practiceId, $departmentId);

            if ( ! isset($ccdaExternal[0])) {
                continue;
            }

            $ccda = Ccda::create([
                'practice_id' => $practice->id,
                'location_id' => $practice->locations->first()->id,
                'user_id'     => auth()->user()->id,
                'xml'         => $ccdaExternal[0]['ccda'],
            ]);

            $imported[] = $ccda->import();
        }

        return $imported;
    }
}

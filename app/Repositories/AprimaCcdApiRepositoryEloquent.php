<?php

namespace App\Repositories;

use App\CarePerson;
use App\Contracts\Repositories\AprimaCcdApiRepository;
use App\ForeignId;
use App\Patient;
use Illuminate\Support\Facades\DB;

class AprimaCcdApiRepositoryEloquent implements AprimaCcdApiRepository
{
    /**
     * @param int $locationId
     * @param string $foreignSystem
     * @return mixed
     */
    public function getPatientAndProviderIdsByLocationAndForeignSystem($locationId, $foreignSystem)
    {
        //Dynamically get all the tables' names since we'll probably change them soon
        $careTeamTable = (new CarePerson())->getTable();
        $foreignIdTable = (new ForeignId())->getTable();
        $patientTable = (new Patient())->getTable();

        $patientAndProviderIds = Patient::select(DB::raw("
                $patientTable.mrn_number as patientId,
                $patientTable.user_id as clhPatientUserId,
                $foreignIdTable.foreign_id as providerId,
                $careTeamTable.member_user_id as clhProviderUserId
                "))
            ->where("$patientTable.preferred_contact_location", $locationId)
            ->whereNotNull("$patientTable.preferred_contact_location")
            ->join($careTeamTable, "$careTeamTable.user_id", '=', "$patientTable.user_id")
            ->where("$careTeamTable.type", '=', CarePerson::BILLING_PROVIDER)
            ->join($foreignIdTable, "$foreignIdTable.user_id", '=', "$careTeamTable.member_user_id")
            ->where("$foreignIdTable.system", '=', $foreignSystem)
            ->where("$foreignIdTable.location_id", '=', $locationId)
            ->whereNotNull("$foreignIdTable.foreign_id")
            ->get();

        return $patientAndProviderIds;
    }
}

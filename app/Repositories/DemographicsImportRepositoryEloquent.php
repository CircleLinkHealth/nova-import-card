<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\DemographicsImportRepository;
use App\ForeignId;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class DemographicsImportRepositoryEloquent.
 */
class DemographicsImportRepositoryEloquent extends BaseRepository implements DemographicsImportRepository
{
    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * @param int    $locationId
     * @param string $foreignSystem
     *
     * @return mixed
     */
    public function getPatientAndProviderIdsByLocationAndForeignSystem($locationId, $foreignSystem)
    {
        //Dynamically get all the tables' names since we'll probably change them soon
        $ccdaTable      = ( new Ccda() )->getTable();
        $patientTable   = ( new DemographicsImport() )->getTable();
        $foreignIdTable = ( new ForeignId() )->getTable();

        return DemographicsImport::select(DB::raw("${patientTable}.mrn_number as patientId,
                ${ccdaTable}.patient_id as clhPatientUserId,
                ${foreignIdTable}.foreign_id as providerId,
                ${patientTable}.provider_id as clhProviderUserId"))
            ->where("${patientTable}.location_id", $locationId)
            ->join($ccdaTable, "${ccdaTable}.id", '=', "${patientTable}.ccda_id")
            ->whereNotNull("${ccdaTable}.patient_id")
            ->join($foreignIdTable, "${foreignIdTable}.user_id", '=', "${patientTable}.provider_id")
            ->where("${foreignIdTable}.system", '=', $foreignSystem)
            ->where("${foreignIdTable}.location_id", '=', $locationId)
            ->whereNotNull("${foreignIdTable}.foreign_id")
            ->get();
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return DemographicsImport::class;
    }
}

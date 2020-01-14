<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CCD;

use App\CLH\CCD\ImportRoutine\CcdImportRoutine;
use App\Traits\Relationships\MedicalRecordItemLoggerRelationships;

/**
 * App\Models\CCD\CcdVendor.
 *
 * @property int                                                                                              $id
 * @property int|null                                                                                         $program_id
 * @property int                                                                                              $ccd_import_routine_id
 * @property string                                                                                           $vendor_name
 * @property string|null                                                                                      $ehr_name
 * @property string|null                                                                                      $practice_id
 * @property int|null                                                                                         $ehr_oid
 * @property string|null                                                                                      $doctor_name
 * @property int|null                                                                                         $doctor_oid
 * @property string|null                                                                                      $custodian_name
 * @property \Carbon\Carbon                                                                                   $created_at
 * @property \Carbon\Carbon                                                                                   $updated_at
 * @property \CircleLinkHealth\CarePlanModels\Entities\AllergyLog[]|\Illuminate\Database\Eloquent\Collection              $allergies
 * @property \App\Importer\Models\ItemLogs\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection         $demographics
 * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection $demographicsImports
 * @property \App\Importer\Models\ItemLogs\DocumentLog[]|\Illuminate\Database\Eloquent\Collection             $document
 * @property \CircleLinkHealth\CarePlanModels\Entities\MedicationLog[]|\Illuminate\Database\Eloquent\Collection           $medications
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog[]|\Illuminate\Database\Eloquent\Collection              $problems
 * @property \App\Importer\Models\ItemLogs\ProviderLog[]|\Illuminate\Database\Eloquent\Collection             $providers
 * @property \App\CLH\CCD\ImportRoutine\CcdImportRoutine                                                      $routine
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereCcdImportRoutineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereCustodianName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereDoctorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereDoctorOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereEhrName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereEhrOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereVendorName($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor query()
 * @property int|null $allergies_count
 * @property int|null $demographics_count
 * @property int|null $demographics_imports_count
 * @property int|null $document_count
 * @property int|null $medications_count
 * @property int|null $problems_count
 * @property int|null $providers_count
 * @property int|null $revision_history_count
 */
class CcdVendor extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use MedicalRecordItemLoggerRelationships;

    protected $guarded = [];

    public function routine()
    {
        return $this->belongsTo(CcdImportRoutine::class, 'ccd_import_routine_id', 'id');
    }
}

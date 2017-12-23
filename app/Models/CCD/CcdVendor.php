<?php namespace App\Models\CCD;

use App\CLH\CCD\ImportRoutine\CcdImportRoutine;
use App\Traits\Relationships\MedicalRecordItemLoggerRelationships;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CCD\CcdVendor
 *
 * @property int $id
 * @property int|null $program_id
 * @property int $ccd_import_routine_id
 * @property string $vendor_name
 * @property string|null $ehr_name
 * @property string|null $practice_id
 * @property int|null $ehr_oid
 * @property string|null $doctor_name
 * @property int|null $doctor_oid
 * @property string|null $custodian_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\AllergyLog[] $allergies
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\DemographicsLog[] $demographics
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ImportedItems\DemographicsImport[] $demographicsImports
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\DocumentLog[] $document
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\MedicationLog[] $medications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\ProblemLog[] $problems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\ProviderLog[] $providers
 * @property-read \App\CLH\CCD\ImportRoutine\CcdImportRoutine $routine
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
 */
class CcdVendor extends \App\BaseModel
{

    use MedicalRecordItemLoggerRelationships;

    protected $guarded = [];

    public function routine()
    {
        return $this->belongsTo(CcdImportRoutine::class, 'ccd_import_routine_id', 'id');
    }
}

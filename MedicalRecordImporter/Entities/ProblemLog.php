<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities;


use CircleLinkHealth\SharedModels\Contracts\Problem;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemCodeLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\SharedModels\HasProblemCodes;
use CircleLinkHealth\Eligibility\BelongsToCcda;


/**
 * CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog.
 *
 * @property int                                                                                     $id
 * @property string|null                                                                             $medical_record_type
 * @property int|null                                                                                $medical_record_id
 * @property int|null                                                                                $vendor_id
 * @property string|null                                                                             $reference
 * @property string|null                                                                             $reference_title
 * @property string|null                                                                             $start
 * @property string|null                                                                             $end
 * @property string|null                                                                             $status
 * @property string|null                                                                             $name
 * @property string|null                                                                             $code
 * @property string|null                                                                             $code_system
 * @property string|null                                                                             $code_system_name
 * @property string|null                                                                             $translation_name
 * @property string|null                                                                             $translation_code
 * @property string|null                                                                             $translation_code_system
 * @property string|null                                                                             $translation_code_system_name
 * @property int                                                                                     $import
 * @property int                                                                                     $invalid
 * @property int                                                                                     $edited
 * @property int|null                                                                                $cpm_problem_id
 * @property string|null                                                                             $deleted_at
 * @property \Carbon\Carbon                                                                          $created_at
 * @property \Carbon\Carbon                                                                          $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda                                                         $ccda
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemCodeLog[]|\Illuminate\Database\Eloquent\Collection $codes
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport                                        $importedItem
 * @property \App\Models\CCD\CcdVendor|null                                                          $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereCpmProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereReferenceTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereTranslationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereTranslationCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereTranslationCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereTranslationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog whereVendorId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog query()
 * @property int|null $codes_count
 * @property int|null $revision_history_count
 */
class ProblemLog extends \CircleLinkHealth\Core\Entities\BaseModel implements Problem
{
    use BelongsToCcda;
    use HasProblemCodes;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'reference',
        'reference_title',
        'start',
        'end',
        'status',
        'name',
        'code',
        'code_system',
        'code_system_name',
        'translation_name',
        'translation_code',
        'translation_code_system',
        'translation_code_system_name',
        'import',
        'invalid',
        'edited',
        'cpm_problem_id',
    ];

    protected $table = 'ccd_problem_logs';

    public function codes()
    {
        return $this->hasMany(ProblemCodeLog::class, 'ccd_problem_log_id');
    }

    public function importedItem()
    {
        return $this->hasOne(ProblemImport::class);
    }
}

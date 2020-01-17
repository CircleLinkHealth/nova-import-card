<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ItemLogs;

use App\Importer\Models\ImportedItems\MedicationImport;
use CircleLinkHealth\Eligibility\BelongsToCcda;

/**
 * App\Importer\Models\ItemLogs\MedicationLog.
 *
 * @property int                                                 $id
 * @property string|null                                         $medical_record_type
 * @property int|null                                            $medical_record_id
 * @property int|null                                            $vendor_id
 * @property string|null                                         $reference
 * @property string|null                                         $reference_title
 * @property string|null                                         $reference_sig
 * @property string|null                                         $start
 * @property string|null                                         $end
 * @property string|null                                         $status
 * @property string|null                                         $text
 * @property string|null                                         $product_name
 * @property string|null                                         $product_code
 * @property string|null                                         $product_code_system
 * @property string|null                                         $product_text
 * @property string|null                                         $translation_name
 * @property string|null                                         $translation_code
 * @property string|null                                         $translation_code_system
 * @property string|null                                         $translation_code_system_name
 * @property int                                                 $import
 * @property int                                                 $invalid
 * @property int                                                 $edited
 * @property string|null                                         $deleted_at
 * @property \Carbon\Carbon                                      $created_at
 * @property \Carbon\Carbon                                      $updated_at
 * @property \App\Models\MedicalRecords\Ccda                     $ccda
 * @property \App\Importer\Models\ImportedItems\MedicationImport $importedItem
 * @property \App\Models\CCD\CcdVendor|null                      $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereProductCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereProductCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereProductText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereReferenceSig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereReferenceTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereTranslationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereTranslationCodeSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereTranslationCodeSystemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereTranslationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog whereVendorId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog query()
 * @property int|null $revision_history_count
 */
class MedicationLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use BelongsToCcda;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'reference',
        'reference_title',
        'reference_sig',
        'start',
        'end',
        'status',
        'text',
        'product_name',
        'product_code',
        'product_code_system',
        'product_text',
        'translation_name',
        'translation_code',
        'translation_code_system',
        'translation_code_system_name',
        'import',
        'invalid',
        'edited',
    ];

    protected $table = 'ccd_medication_logs';

    public function importedItem()
    {
        return $this->hasOne(MedicationImport::class);
    }
}

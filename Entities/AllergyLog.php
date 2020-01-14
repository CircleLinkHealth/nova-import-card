<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;


use CircleLinkHealth\SharedModels\Entities\AllergyImport;
use CircleLinkHealth\Eligibility\BelongsToCcda;


/**
 * CircleLinkHealth\SharedModels\Entities\AllergyLog.
 *
 * @property int                                              $id
 * @property string|null                                      $medical_record_type
 * @property int|null                                         $medical_record_id
 * @property int|null                                         $vendor_id
 * @property string|null                                      $start
 * @property string|null                                      $end
 * @property string|null                                      $status
 * @property string|null                                      $allergen_name
 * @property int                                              $import
 * @property int                                              $invalid
 * @property int                                              $edited
 * @property string|null                                      $deleted_at
 * @property \Carbon\Carbon                                   $created_at
 * @property \Carbon\Carbon                                   $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda                  $ccda
 * @property \CircleLinkHealth\SharedModels\Entities\AllergyImport $importedItem
 * @property \App\Models\CCD\CcdVendor|null                   $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereAllergenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog whereVendorId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog query()
 * @property int|null $revision_history_count
 */
class AllergyLog extends \CircleLinkHealth\Core\Entities\BaseModel 
{
    use BelongsToCcda;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'start',
        'end',
        'status',
        'allergen_name',
        'import',
        'invalid',
        'edited',
    ];

    protected $table = 'ccd_allergy_logs';

    public function importedItem()
    {
        return $this->hasOne(AllergyImport::class);
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ItemLogs;

use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;

/**
 * App\Importer\Models\ItemLogs\InsuranceLog.
 *
 * @property int                                                        $id
 * @property string|null                                                $medical_record_type
 * @property int|null                                                   $medical_record_id
 * @property string                                                     $name
 * @property string|null                                                $type
 * @property string|null                                                $policy_id
 * @property string|null                                                $relation
 * @property string|null                                                $subscriber
 * @property int                                                        $import
 * @property \Carbon\Carbon|null                                        $created_at
 * @property \Carbon\Carbon|null                                        $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda               $ccda
 * @property \CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy $importedItem
 * @property \App\Models\CCD\CcdVendor                                  $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog wherePolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereSubscriber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog query()
 * @property array|null $raw
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog whereRaw($value)
 * @property int|null $revision_history_count
 */
class InsuranceLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $casts = [
        'raw' => 'array',
    ];

    protected $fillable = [
        'medical_record_id',
        'medical_record_type',
        'name',
        'type',
        'policy_id',
        'relation',
        'subscriber',
        'import',
        'raw',
    ];

    public function importedItem()
    {
        return $this->hasOne(CcdInsurancePolicy::class);
    }
}

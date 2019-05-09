<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ItemLogs;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Traits\Relationships\BelongsToCcda;
use App\Traits\Relationships\BelongsToVendor;

/**
 * App\Importer\Models\ItemLogs\DocumentLog.
 *
 * @property int                             $id
 * @property int                             $ml_ignore
 * @property int|null                        $location_id
 * @property int|null                        $practice_id
 * @property int|null                        $billing_provider_id
 * @property string|null                     $medical_record_type
 * @property int|null                        $medical_record_id
 * @property int|null                        $vendor_id
 * @property string                          $type
 * @property string                          $custodian
 * @property int                             $import
 * @property int                             $invalid
 * @property int                             $edited
 * @property string|null                     $deleted_at
 * @property \Carbon\Carbon                  $created_at
 * @property \Carbon\Carbon                  $updated_at
 * @property \App\Models\MedicalRecords\Ccda $ccda
 * @property \App\Models\CCD\CcdVendor|null  $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereCustodian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereMlIgnore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog whereVendorId($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DocumentLog query()
 */
class DocumentLog extends \CircleLinkHealth\Core\Entities\BaseModel implements ItemLog
{
    use BelongsToCcda;
    use
        BelongsToVendor;

    protected $guarded = [];

    protected $table = 'ccd_document_logs';
}

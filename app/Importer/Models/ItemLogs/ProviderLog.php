<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ItemLogs;

use CircleLinkHealth\Eligibility\BelongsToCcda;

/**
 * App\Importer\Models\ItemLogs\ProviderLog.
 *
 * @property int                                           $id
 * @property int                                           $ml_ignore
 * @property int|null                                      $location_id
 * @property int|null                                      $practice_id
 * @property int|null                                      $billing_provider_id
 * @property int|null                                      $user_id
 * @property string|null                                   $medical_record_type
 * @property int|null                                      $medical_record_id
 * @property int|null                                      $vendor_id
 * @property string|null                                   $npi
 * @property string|null                                   $provider_id
 * @property string|null                                   $first_name
 * @property string|null                                   $last_name
 * @property string|null                                   $organization
 * @property string|null                                   $street
 * @property string|null                                   $city
 * @property string|null                                   $state
 * @property string|null                                   $zip
 * @property string|null                                   $cell_phone
 * @property string|null                                   $home_phone
 * @property string|null                                   $work_phone
 * @property int                                           $import
 * @property int                                           $invalid
 * @property int                                           $edited
 * @property string|null                                   $deleted_at
 * @property \Carbon\Carbon                                $created_at
 * @property \Carbon\Carbon                                $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda  $ccda
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $providerLoggable
 * @property \App\Models\CCD\CcdVendor|null                $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereMlIgnore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereNpi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereOrganization($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog whereZip($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProviderLog query()
 *
 * @property int|null $revision_history_count
 */
class ProviderLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use BelongsToCcda;

    protected $fillable = [
        'location_id',
        'practice_id',
        'billing_provider_id',
        'user_id',
        'medical_record_type',
        'medical_record_id',
        'vendor_id',
        'npi',
        'provider_id',
        'first_name',
        'last_name',
        'organization',
        'street',
        'city',
        'state',
        'zip',
        'cell_phone',
        'home_phone',
        'work_phone',
        'import',
        'invalid',
        'edited',
        'ml_ignore',
    ];

    protected $table = 'ccd_provider_logs';

    /**
     * Get all of the owning commentable models.
     */
    public function providerLoggable()
    {
        return $this->morphTo();
    }
}

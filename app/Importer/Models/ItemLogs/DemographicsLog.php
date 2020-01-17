<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ItemLogs;

use App\Importer\Models\ImportedItems\DemographicsImport;
use CircleLinkHealth\Eligibility\BelongsToCcda;

/**
 * App\Importer\Models\ItemLogs\DemographicsLog.
 *
 * @property int                                                   $id
 * @property string|null                                           $medical_record_type
 * @property int|null                                              $medical_record_id
 * @property int|null                                              $vendor_id
 * @property string|null                                           $first_name
 * @property string|null                                           $last_name
 * @property string|null                                           $dob
 * @property string|null                                           $gender
 * @property string|null                                           $mrn_number
 * @property string|null                                           $street
 * @property string|null                                           $street2
 * @property string|null                                           $city
 * @property string|null                                           $state
 * @property string|null                                           $zip
 * @property string|null                                           $primary_phone
 * @property string|null                                           $cell_phone
 * @property string|null                                           $home_phone
 * @property string|null                                           $work_phone
 * @property string|null                                           $email
 * @property string|null                                           $language
 * @property string                                                $consent_date
 * @property string|null                                           $race
 * @property string|null                                           $ethnicity
 * @property int                                                   $import
 * @property int                                                   $invalid
 * @property int                                                   $edited
 * @property string|null                                           $deleted_at
 * @property \Carbon\Carbon                                        $created_at
 * @property \Carbon\Carbon                                        $updated_at
 * @property string|null                                           $preferred_call_times
 * @property string|null                                           $preferred_call_days
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda          $ccda
 * @property \App\Importer\Models\ImportedItems\DemographicsImport $importedItem
 * @property \App\Models\CCD\CcdVendor|null                        $vendor
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereConsentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereEdited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereEthnicity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereMrnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog wherePreferredCallDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog wherePreferredCallTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereRace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereStreet2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog whereZip($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog query()
 * @property int|null $revision_history_count
 */
class DemographicsLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use BelongsToCcda;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'mrn_number',
        'street',
        'street2',
        'city',
        'state',
        'zip',
        'cell_phone',
        'home_phone',
        'work_phone',
        'primary_phone',
        'email',
        'language',
        'race',
        'consent_date',
        'ethnicity',
        'preferred_call_times',
        'preferred_call_days',
    ];

    protected $table = 'ccd_demographics_logs';

    public function importedItem()
    {
        return $this->hasOne(DemographicsImport::class);
    }
}

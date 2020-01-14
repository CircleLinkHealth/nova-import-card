<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Models\ImportedItems;

use App\Importer\Models\ItemLogs\DemographicsLog;
use CircleLinkHealth\Eligibility\BelongsToCcda;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Importer\Models\ImportedItems\DemographicsImport.
 *
 * @property int                                           $id
 * @property string|null                                   $medical_record_type
 * @property int|null                                      $medical_record_id
 * @property int                                           $imported_medical_record_id
 * @property int|null                                      $vendor_id
 * @property int|null                                      $program_id
 * @property int|null                                      $provider_id
 * @property int|null                                      $location_id
 * @property string|null                                   $first_name
 * @property string|null                                   $last_name
 * @property string|null                                   $dob
 * @property string|null                                   $gender
 * @property string|null                                   $mrn_number
 * @property string|null                                   $street
 * @property string|null                                   $city
 * @property string|null                                   $state
 * @property string|null                                   $zip
 * @property string|null                                   $primary_phone
 * @property string|null                                   $cell_phone
 * @property string|null                                   $home_phone
 * @property string|null                                   $work_phone
 * @property string|null                                   $email
 * @property string|null                                   $preferred_contact_timezone
 * @property string|null                                   $consent_date
 * @property string|null                                   $preferred_contact_language
 * @property string|null                                   $study_phone_number
 * @property int|null                                      $substitute_id
 * @property \Carbon\Carbon                                $created_at
 * @property \Carbon\Carbon                                $updated_at
 * @property string|null                                   $preferred_call_times
 * @property string|null                                   $preferred_call_days
 * @property \App\Importer\Models\ItemLogs\DemographicsLog $ccdLog
 * @property \CircleLinkHealth\CarePlanModels\Entities\Ccda               $ccda
 * @property \CircleLinkHealth\Customer\Entities\User|null $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereConsentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereImportedMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereMrnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport wherePreferredCallDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport wherePreferredCallTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport wherePreferredContactLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport wherePreferredContactTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereStudyPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereSubstituteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport whereZip($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport query()
 * @property int|null $revision_history_count
 */
class DemographicsImport extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use CircleLinkHealth\Eligibility\BelongsToCcda;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'imported_medical_record_id',
        'vendor_id',
        'program_id',
        'provider_id',
        'location_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'mrn_number',
        'street',
        'city',
        'state',
        'zip',
        'primary_phone',
        'cell_phone',
        'home_phone',
        'work_phone',
        'email',
        'preferred_contact_timezone',
        'consent_date',
        'preferred_contact_language',
        'study_phone_number',
        'substitute_id',
        'preferred_call_times',
        'preferred_call_days',
    ];

    public function ccdLog()
    {
        return $this->belongsTo(DemographicsLog::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}

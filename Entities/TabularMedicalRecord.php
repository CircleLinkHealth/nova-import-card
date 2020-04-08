<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Importer\Loggers\Csv\TabularMedicalRecordSectionsLogger;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordLogger;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicalRecordEloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\SharedModels\Entities\TabularMedicalRecord.
 *
 * @property int                                                                                                                $id
 * @property int|null                                                                                                           $practice_id
 * @property int|null                                                                                                           $location_id
 * @property int|null                                                                                                           $billing_provider_id
 * @property int|null                                                                                                           $uploaded_by
 * @property int|null                                                                                                           $patient_id
 * @property string|null                                                                                                        $patient_name
 * @property string|null                                                                                                        $first_name
 * @property string|null                                                                                                        $last_name
 * @property \Carbon\Carbon|null                                                                                                $dob
 * @property string|null                                                                                                        $problems_string
 * @property string|null                                                                                                        $medications_string
 * @property string|null                                                                                                        $allergies_string
 * @property string|null                                                                                                        $provider_name
 * @property string|null                                                                                                        $mrn
 * @property string|null                                                                                                        $gender
 * @property string|null                                                                                                        $language
 * @property \Carbon\Carbon                                                                                                     $consent_date
 * @property string|null                                                                                                        $primary_phone
 * @property string|null                                                                                                        $cell_phone
 * @property string|null                                                                                                        $home_phone
 * @property string|null                                                                                                        $work_phone
 * @property string|null                                                                                                        $email
 * @property string|null                                                                                                        $address
 * @property string|null                                                                                                        $address2
 * @property string|null                                                                                                        $city
 * @property string|null                                                                                                        $state
 * @property string|null                                                                                                        $zip
 * @property string|null                                                                                                        $primary_insurance
 * @property string|null                                                                                                        $secondary_insurance
 * @property string|null                                                                                                        $tertiary_insurance
 * @property \Carbon\Carbon|null                                                                                                $created_at
 * @property \Carbon\Carbon|null                                                                                                $updated_at
 * @property string|null                                                                                                        $preferred_call_times
 * @property string|null                                                                                                        $preferred_call_days
 * @property \CircleLinkHealth\SharedModels\Entities\AllergyLog[]|\Illuminate\Database\Eloquent\Collection                      $allergies
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection                           $demographics
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection                   $demographicsImports
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DocumentLog[]|\Illuminate\Database\Eloquent\Collection                               $document
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog[]|\Illuminate\Database\Eloquent\Collection                   $medications
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog[]|\Illuminate\Database\Eloquent\Collection $problems
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog[]|\Illuminate\Database\Eloquent\Collection                               $providers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereAllergiesString($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereConsentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereMedicationsString($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord wherePreferredCallDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord wherePreferredCallTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord wherePrimaryPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereProblemsString($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereTertiaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereZip($value)
 * @mixin \Eloquent
 * @property string|null                                                                    $deleted_at
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\TabularMedicalRecord onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\TabularMedicalRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\TabularMedicalRecord withoutTrashed()
 * @property int|null $allergies_count
 * @property int|null $demographics_count
 * @property int|null $demographics_imports_count
 * @property int|null $document_count
 * @property int|null $medications_count
 * @property int|null $problems_count
 * @property int|null $providers_count
 * @property int|null $revision_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicalRecordEloquent hasUPG0506PdfCareplanMedia()
 */
class TabularMedicalRecord extends MedicalRecordEloquent
{
    use SoftDeletes;

    protected $dates = [
        'dob',
        'consent_date',
    ];

    protected $fillable = [
        'practice_id',
        'location_id',
        'billing_provider_id',

        'uploaded_by',

        'patient_id',

        'mrn',
        'first_name',
        'last_name',
        'dob',

        'allergies_string',
        'medications_string',
        'problems_string',

        'gender',
        'language',
        'consent_date',

        'provider_name',

        'primary_phone',
        'cell_phone',
        'home_phone',
        'work_phone',
        'email',

        'address',
        'address2',
        'city',
        'state',
        'zip',

        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',

        'preferred_call_times',
        'preferred_call_days',
    ];

    public function getDocumentCustodian(): string
    {
        return '';
    }

    /**
     * Get the Transformer.
     */
    public function getLogger(): MedicalRecordLogger
    {
        return new TabularMedicalRecordSectionsLogger($this, Practice::find($this->practice_id));
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     */
    public function getPatient():?User
    {
        // TODO: Implement show() method.
    }

    public function getReferringProviderName()
    {
        return $this->provider_name;
    }

    /**
     * @return mixed
     */
    public function importedMedicalRecord()
    {
        return ImportedMedicalRecord::where('medical_record_type', '=', TabularMedicalRecord::class)
            ->where('medical_record_id', '=', $this->id)
            ->first();
    }

    public function setPatientNameAttribute(string $value): string
    {
        if ($value) {
            $names            = explode(', ', $value);
            $this->first_name = $names[0];
            $this->last_name  = $names[1];
        }

        return $value;
    }
}

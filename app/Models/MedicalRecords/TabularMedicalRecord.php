<?php

namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Loggers\Csv\PhoenixHeartSectionsLogger;
use App\Importer\Loggers\Csv\RappaSectionsLogger;
use App\Importer\Loggers\Csv\TabularMedicalRecordSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\Practice;
use App\User;

/**
 * App\Models\MedicalRecords\TabularMedicalRecord
 *
 * @property int $id
 * @property int|null $practice_id
 * @property int|null $location_id
 * @property int|null $billing_provider_id
 * @property int|null $uploaded_by
 * @property int|null $patient_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property \Carbon\Carbon|null $dob
 * @property string|null $problems_string
 * @property string|null $medications_string
 * @property string|null $allergies_string
 * @property string|null $provider_name
 * @property string|null $mrn
 * @property string|null $gender
 * @property string|null $language
 * @property \Carbon\Carbon $consent_date
 * @property string|null $primary_phone
 * @property string|null $cell_phone
 * @property string|null $home_phone
 * @property string|null $work_phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $primary_insurance
 * @property string|null $secondary_insurance
 * @property string|null $tertiary_insurance
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $preferred_call_times
 * @property string|null $preferred_call_days
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\AllergyLog[] $allergies
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\DemographicsLog[] $demographics
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ImportedItems\DemographicsImport[] $demographicsImports
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\DocumentLog[] $document
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\MedicationLog[] $medications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\ProblemLog[] $problems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\ProviderLog[] $providers
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
 */
class TabularMedicalRecord extends MedicalRecordEloquent
{
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

    /**
     * Get the Transformer
     *
     * @return MedicalRecordLogger
     */
    public function getLogger(): MedicalRecordLogger
    {
        $phoenixHeart = Practice::whereDisplayName('Phoenix Heart')->first();

        if ($phoenixHeart && $this->practice_id == $phoenixHeart->id) {
            return new PhoenixHeartSectionsLogger($this, $phoenixHeart);
        }

        $rappahannock = Practice::whereDisplayName('Rappahannock Family Physicians')->first();

        if ($rappahannock && $this->practice_id == $rappahannock->id) {
            return new RappaSectionsLogger($this, $rappahannock);
        }

        return new TabularMedicalRecordSectionsLogger($this, Practice::find($this->practice_id));
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient(): User
    {
        // TODO: Implement getPatient() method.
    }

    public function getDocumentCustodian(): string
    {
        return '';
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
}

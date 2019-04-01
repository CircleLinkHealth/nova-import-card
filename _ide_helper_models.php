<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */

namespace App{
    /**
     * App\PatientReports.
     *
     * @property int            $id
     * @property int            $patient_id
     * @property string         $patient_mrn
     * @property string         $provider_id
     * @property string         $file_type
     * @property int            $location_id
     * @property string         $file_base64
     * @property string|null    $deleted_at
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\PatientReports onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereFileBase64($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereFileType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereLocationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports wherePatientMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\PatientReports withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\PatientReports withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientReports query()
     */
    class PatientReports extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\DatabaseNotification.
     *
     * @property string                                        $id
     * @property string                                        $type
     * @property int                                           $notifiable_id
     * @property string                                        $notifiable_type
     * @property int|null                                      $attachment_id
     * @property string|null                                   $attachment_type
     * @property array                                         $data
     * @property \Illuminate\Support\Carbon|null               $read_at
     * @property \Illuminate\Support\Carbon|null               $created_at
     * @property \Illuminate\Support\Carbon|null               $updated_at
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $attachment
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $notifiable
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification hasAttachmentType($type)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification hasNotifiableType($type)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereAttachmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereAttachmentType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereNotifiableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereNotifiableType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereReadAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DatabaseNotification whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class DatabaseNotification extends \Eloquent
    {
    }
}

namespace App{
    /**
     * Represents a participant of a conference call.
     *
     * @property string                                                                         $call_sid
     * @property string                                                                         $account_sid
     * @property string                                                                         $conference_sid
     * @property string                                                                         $participant_number
     * @property string                                                                         $status
     * @property int                                                                            $duration
     * @property int                                                                            $id
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereAccountSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereCallSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereConferenceSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereParticipantNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioConferenceCallParticipant whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TwilioConferenceCallParticipant extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\MedicationGroupsMap.
     *
     * @property int                                $id
     * @property string                             $keyword
     * @property int                                $medication_group_id
     * @property \Carbon\Carbon|null                $created_at
     * @property \Carbon\Carbon|null                $updated_at
     * @property \App\Models\CPM\CpmMedicationGroup $cpmMedicationGroup
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereKeyword($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereMedicationGroupId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\MedicationGroupsMap query()
     */
    class MedicationGroupsMap extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Observation.
     *
     * @property int                                                                            $id
     * @property string                                                                         $obs_date
     * @property string                                                                         $obs_date_gmt
     * @property int                                                                            $comment_id
     * @property int                                                                            $sequence_id
     * @property string                                                                         $obs_message_id
     * @property int                                                                            $user_id
     * @property string                                                                         $obs_method
     * @property string                                                                         $obs_key
     * @property string                                                                         $obs_value
     * @property string                                                                         $obs_unit
     * @property int                                                                            $program_id
     * @property int                                                                            $legacy_obs_id
     * @property \Illuminate\Support\Carbon                                                     $created_at
     * @property \Illuminate\Support\Carbon                                                     $updated_at
     * @property \App\Comment                                                                   $comment
     * @property mixed                                                                          $alert_level
     * @property mixed                                                                          $alert_log
     * @property mixed                                                                          $alert_sort_weight
     * @property mixed                                                                          $alert_status_change
     * @property mixed                                                                          $alert_status_history
     * @property mixed                                                                          $starting_observation
     * @property mixed                                                                          $timezone
     * @property \App\ObservationMeta[]|\Illuminate\Database\Eloquent\Collection                $meta
     * @property \App\CPRulesQuestions                                                          $question
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\User                                       $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereCommentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereLegacyObsId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsDateGmt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsMessageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsMethod($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsUnit($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsValue($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereSequenceId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Observation whereUserId($value)
     * @mixin \Eloquent
     */
    class Observation extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Note.
     *
     * @property int                                                             $id
     * @property int                                                             $patient_id
     * @property int                                                             $author_id
     * @property string                                                          $body
     * @property int                                                             $isTCM
     * @property int                                                             $did_medication_recon
     * @property \Carbon\Carbon                                                  $created_at
     * @property \Carbon\Carbon                                                  $updated_at
     * @property string                                                          $type
     * @property string                                                          $performed_at
     * @property int|null                                                        $logger_id
     * @property \App\Models\Addendum[]|\Illuminate\Database\Eloquent\Collection $addendums
     * @property \CircleLinkHealth\Customer\Entities\User                        $author
     * @property \App\Call                                                       $call
     * @property \CircleLinkHealth\Customer\Entities\User                        $patient
     * @property \CircleLinkHealth\Customer\Entities\User                        $program
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereAuthorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereBody($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereDidMedicationRecon($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereIsTCM($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereLoggerId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note wherePerformedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \CircleLinkHealth\Customer\Entities\User|null                                        $logger
     * @property \App\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]       $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note emergency($yes = true)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note forwarded(\Carbon\Carbon $from = null, \Carbon\Carbon $to = null, $excludePatientSupport = true)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note forwardedTo($notifiableType, $notifiableId, \Carbon\Carbon $from = null, \Carbon\Carbon $to = null)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note patientPractice($practiceId)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Note query()
     */
    class Note extends \Eloquent
    {
    }
}

namespace App{
    /**
     * Enrollment Tips per Practice.
     *
     * @property int practice_id
     * @property string content
     * @property int                                                                            $id
     * @property int                                                                            $practice_id
     * @property string                                                                         $content
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereContent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PracticeEnrollmentTips whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class PracticeEnrollmentTips extends \Eloquent
    {
    }
}

namespace App{
    /**
     * Unstructured twilio logs (raw).
     *
     * @property string                                                                         $call_sid
     * @property string                                                                         $application_sid
     * @property string                                                                         $account_sid
     * @property string                                                                         $call_status
     * @property string                                                                         $log
     * @property int                                                                            $id
     * @property string|null                                                                    $type
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereAccountSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereApplicationSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereCallSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereCallStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereLog($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRawLog whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TwilioRawLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ImportedItems{
    /**
     * App\Importer\Models\ImportedItems\AllergyImport.
     *
     * @property int                                      $id
     * @property string|null                              $medical_record_type
     * @property int|null                                 $medical_record_id
     * @property int                                      $imported_medical_record_id
     * @property int|null                                 $vendor_id
     * @property int                                      $ccd_allergy_log_id
     * @property string|null                              $allergen_name
     * @property int|null                                 $substitute_id
     * @property string|null                              $deleted_at
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \App\Importer\Models\ItemLogs\AllergyLog $ccdLog
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereAllergenName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereCcdAllergyLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereImportedMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereSubstituteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport whereVendorId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\AllergyImport query()
     */
    class AllergyImport extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ImportedItems{
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
     * @property \App\Models\MedicalRecords\Ccda               $ccda
     * @property \CircleLinkHealth\Customer\Entities\User|null $provider
     *
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
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\DemographicsImport query()
     */
    class DemographicsImport extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ImportedItems{
    /**
     * App\Importer\Models\ImportedItems\MedicationImport.
     *
     * @property int                                         $id
     * @property string|null                                 $medical_record_type
     * @property int|null                                    $medical_record_id
     * @property int                                         $imported_medical_record_id
     * @property int|null                                    $vendor_id
     * @property int                                         $ccd_medication_log_id
     * @property int|null                                    $medication_group_id
     * @property string|null                                 $name
     * @property string|null                                 $sig
     * @property string|null                                 $code
     * @property string|null                                 $code_system
     * @property string|null                                 $code_system_name
     * @property int|null                                    $substitute_id
     * @property string|null                                 $deleted_at
     * @property \Carbon\Carbon                              $created_at
     * @property \Carbon\Carbon                              $updated_at
     * @property \App\Importer\Models\ItemLogs\MedicationLog $ccdLog
     * @property \App\Models\CPM\CpmMedicationGroup|null     $cpmMedicationGroup
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCcdMedicationLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCodeSystem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCodeSystemName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereImportedMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereMedicationGroupId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereSig($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereSubstituteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport whereVendorId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\MedicationImport query()
     */
    class MedicationImport extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ImportedItems{
    /**
     * App\Importer\Models\ImportedItems\ProblemImport.
     *
     * @property int                                      $id
     * @property string|null                              $medical_record_type
     * @property int|null                                 $medical_record_id
     * @property int                                      $imported_medical_record_id
     * @property int                                      $ccd_problem_log_id
     * @property string|null                              $name
     * @property string|null                              $code
     * @property string|null                              $code_system
     * @property string|null                              $code_system_name
     * @property int                                      $activate
     * @property int|null                                 $cpm_problem_id
     * @property int|null                                 $substitute_id
     * @property string|null                              $deleted_at
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \App\Importer\Models\ItemLogs\ProblemLog $ccdLog
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereActivate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCcdProblemLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCodeSystem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCodeSystemName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCpmProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereImportedMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereSubstituteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ImportedItems\ProblemImport query()
     */
    class ProblemImport extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
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
     * @property \App\Models\MedicalRecords\Ccda               $ccda
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
     */
    class ProviderLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
    /**
     * App\Importer\Models\ItemLogs\ProblemCodeLog.
     *
     * @property int                                           $id
     * @property int|null                                      $ccd_problem_log_id
     * @property string                                        $code_system_name
     * @property string|null                                   $code_system_oid
     * @property string                                        $code
     * @property string|null                                   $name
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property \App\Importer\Models\ItemLogs\ProblemLog|null $problemLog
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCcdProblemLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCodeSystemName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCodeSystemOid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property int|null                                                                       $problem_code_system_id
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemCodeLog whereProblemCodeSystemId($value)
     */
    class ProblemCodeLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
    /**
     * App\Importer\Models\ItemLogs\ProblemLog.
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
     * @property \App\Models\MedicalRecords\Ccda                                                         $ccda
     * @property \App\Importer\Models\ItemLogs\ProblemCodeLog[]|\Illuminate\Database\Eloquent\Collection $codes
     * @property \App\Importer\Models\ImportedItems\ProblemImport                                        $importedItem
     * @property \App\Models\CCD\CcdVendor|null                                                          $vendor
     *
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
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\ProblemLog query()
     */
    class ProblemLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
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
     * @property \App\Models\MedicalRecords\Ccda                       $ccda
     * @property \App\Importer\Models\ImportedItems\DemographicsImport $importedItem
     * @property \App\Models\CCD\CcdVendor|null                        $vendor
     *
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
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\DemographicsLog query()
     */
    class DemographicsLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
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
    class DocumentLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
    /**
     * App\Importer\Models\ItemLogs\AllergyLog.
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
     * @property \App\Models\MedicalRecords\Ccda                  $ccda
     * @property \App\Importer\Models\ImportedItems\AllergyImport $importedItem
     * @property \App\Models\CCD\CcdVendor|null                   $vendor
     *
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
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\AllergyLog query()
     */
    class AllergyLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
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
     *
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
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\MedicationLog query()
     */
    class MedicationLog extends \Eloquent
    {
    }
}

namespace App\Importer\Models\ItemLogs{
    /**
     * App\Importer\Models\ItemLogs\InsuranceLog.
     *
     * @property int                                $id
     * @property string|null                        $medical_record_type
     * @property int|null                           $medical_record_id
     * @property string                             $name
     * @property string|null                        $type
     * @property string|null                        $policy_id
     * @property string|null                        $relation
     * @property string|null                        $subscriber
     * @property int                                $import
     * @property \Carbon\Carbon|null                $created_at
     * @property \Carbon\Carbon|null                $updated_at
     * @property \App\Models\MedicalRecords\Ccda    $ccda
     * @property \App\Models\CCD\CcdInsurancePolicy $importedItem
     * @property \App\Models\CCD\CcdVendor          $vendor
     *
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
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Importer\Models\ItemLogs\InsuranceLog query()
     */
    class InsuranceLog extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CcmTimeApiLog.
     *
     * @property int                                              $id
     * @property int                                              $activity_id
     * @property \Carbon\Carbon                                   $created_at
     * @property \Carbon\Carbon                                   $updated_at
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity $activity
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereActivityId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog query()
     */
    class CcmTimeApiLog extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\EligibilityBatch.
     *
     * @property int                                                                            $id
     * @property int|null                                                                       $initiator_id
     * @property int|null                                                                       $practice_id
     * @property string                                                                         $type
     * @property int                                                                            $status
     * @property array                                                                          $options
     * @property array                                                                          $stats
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property string|null                                                                    $deleted_at
     * @property \App\EligibilityJob[]|\Illuminate\Database\Eloquent\Collection                 $eligibilityJobs
     * @property \CircleLinkHealth\Customer\Entities\User                                       $initiatorUser
     * @property \CircleLinkHealth\Customer\Entities\Practice|null                              $practice
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereInitiatorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereOptions($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereStats($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityBatch whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class EligibilityBatch extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\EnrolleeCustomFilter.
     *
     * @property string                                                                                  $name
     * @property string                                                                                  $type
     * @property int                                                                                     $id
     * @property \Illuminate\Support\Carbon|null                                                         $created_at
     * @property \Illuminate\Support\Carbon|null                                                         $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeCustomFilter whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class EnrolleeCustomFilter extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\AppConfig.
     *
     * @property int            $id
     * @property string         $config_key
     * @property string         $config_value
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereConfigKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereConfigValue($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\AppConfig query()
     */
    class AppConfig extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CarePlanPrintListView.
     *
     * @property int         $care_plan_id
     * @property string      $care_plan_status
     * @property string|null $last_printed
     * @property string|null $provider_date
     * @property int|null    $patient_id
     * @property string|null $patient_full_name
     * @property string|null $patient_first_name
     * @property string|null $patient_last_name
     * @property string|null $patient_registered
     * @property int|null    $patient_info_id
     * @property string|null $patient_dob
     * @property string|null $patient_ccm_status
     * @property int|null    $primary_practice_id
     * @property string|null $practice_name
     * @property string|null $approver_full_name
     * @property string|null $provider_full_name
     * @property int|null    $patient_ccm_time
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereApproverFullName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereCarePlanId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereCarePlanStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereLastPrinted($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientCcmStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientCcmTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientDob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientFullName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientInfoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePatientRegistered($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePracticeName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView wherePrimaryPracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereProviderDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanPrintListView whereProviderFullName($value)
     * @mixin \Eloquent
     */
    class CarePlanPrintListView extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CareAmbassador.
     *
     * @property int                                                               $id
     * @property int                                                               $user_id
     * @property int|null                                                          $hourly_rate
     * @property int                                                               $speaks_spanish
     * @property \Carbon\Carbon|null                                               $created_at
     * @property \Carbon\Carbon|null                                               $updated_at
     * @property \App\CareAmbassadorLog[]|\Illuminate\Database\Eloquent\Collection $logs
     * @property \CircleLinkHealth\Customer\Entities\User                          $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereHourlyRate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereSpeaksSpanish($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador query()
     */
    class CareAmbassador extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CareplanAssessment.
     *
     * @property int                                           $id
     * @property int                                           $careplan_id
     * @property int                                           $provider_approver_id
     * @property string                                        $alcohol_misuse_counseling
     * @property string                                        $diabetes_screening_interval
     * @property \Carbon\Carbon                                $diabetes_screening_last_date
     * @property \Carbon\Carbon                                $diabetes_screening_next_date
     * @property array|string|null                             $diabetes_screening_risk
     * @property string                                        $eye_screening_last_date
     * @property string                                        $eye_screening_next_date
     * @property string                                        $key_treatment
     * @property array|string|null                             $patient_functional_assistance_areas
     * @property array|string|null                             $patient_psychosocial_areas_to_watch
     * @property string                                        $risk
     * @property array|string|null                             $risk_factors
     * @property string                                        $tobacco_misuse_counseling
     * @property \Carbon\Carbon                                $created_at
     * @property \Carbon\Carbon                                $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User|null $approver
     * @property \CircleLinkHealth\Customer\Entities\User|null $patient
     * @property \App\CarePlan|null                            $carePlan
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereAlcoholMisuseCounseling($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereCareplanId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningInterval($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningLastDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningNextDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereDiabetesScreeningRisk($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereEyeScreeningLastDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereEyeScreeningNextDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereKeyTreatment($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment wherePatientFunctionalAssistanceAreas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment wherePatientPsychosocialAreasToWatch($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereProviderApproverId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereRisk($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereRiskFactors($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereTobaccoMisuseCounseling($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareplanAssessment whereUpdatedAt($value)
     */
    class CareplanAssessment extends \Eloquent
    {
    }
}

namespace App\CLH\CCD\Importer{
    /**
     * App\CLH\CCD\Importer\SnomedToICD10Map.
     *
     * @property int    $snomed_code
     * @property string $snomed_name
     * @property string $icd_10_code
     * @property string $icd_10_name
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereIcd10Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereIcd10Name($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereSnomedCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map whereSnomedName($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToICD10Map query()
     */
    class SnomedToICD10Map extends \Eloquent
    {
    }
}

namespace App\CLH\CCD\Importer{
    /**
     * App\CLH\CCD\Importer\SnomedToCpmIcdMap.
     *
     * @property int                             $id
     * @property int                             $snomed_code
     * @property string                          $snomed_name
     * @property string                          $icd_10_code
     * @property string                          $icd_10_name
     * @property \Carbon\Carbon                  $created_at
     * @property \Carbon\Carbon                  $updated_at
     * @property string                          $icd_9_code
     * @property string                          $icd_9_name
     * @property float                           $icd_9_avg_usage
     * @property int                             $icd_9_is_nec
     * @property int|null                        $cpm_problem_id
     * @property \App\Models\CPM\CpmProblem|null $cpmProblem
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereCpmProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd10Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd10Name($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9AvgUsage($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9IsNec($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereIcd9Name($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereSnomedCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereSnomedName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\Importer\SnomedToCpmIcdMap query()
     */
    class SnomedToCpmIcdMap extends \Eloquent
    {
    }
}

namespace App\CLH\CCD\ImportRoutine{
    /**
     * App\CLH\CCD\ImportRoutine\CcdImportStrategies.
     *
     * @property int            $id
     * @property int            $ccd_import_routine_id
     * @property int            $importer_section_id
     * @property int            $validator_id
     * @property int            $parser_id
     * @property int            $storage_id
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereCcdImportRoutineId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereImporterSectionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereParserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereStorageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies whereValidatorId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportStrategies query()
     */
    class CcdImportStrategies extends \Eloquent
    {
    }
}

namespace App\CLH\CCD\ImportRoutine{
    /**
     * App\CLH\CCD\ImportRoutine\CcdImportRoutine.
     *
     * @property int                                                                                       $id
     * @property string                                                                                    $name
     * @property string                                                                                    $description
     * @property \Carbon\Carbon                                                                            $created_at
     * @property \Carbon\Carbon                                                                            $updated_at
     * @property \App\CLH\CCD\ImportRoutine\CcdImportStrategies[]|\Illuminate\Database\Eloquent\Collection $strategies
     * @property \App\Models\CCD\CcdVendor[]|\Illuminate\Database\Eloquent\Collection                      $vendors
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CLH\CCD\ImportRoutine\CcdImportRoutine query()
     */
    class CcdImportRoutine extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\DirectMailMessage.
     *
     * @property int                                                                        $id
     * @property string                                                                     $message_id
     * @property string                                                                     $from
     * @property string                                                                     $to
     * @property string                                                                     $subject
     * @property string|null                                                                $body
     * @property int|null                                                                   $num_attachments
     * @property \Illuminate\Support\Carbon|null                                            $created_at
     * @property \Illuminate\Support\Carbon|null                                            $updated_at
     * @property \App\Models\MedicalRecords\Ccda[]|\Illuminate\Database\Eloquent\Collection $ccdas
     * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                      $media
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereBody($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereFrom($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereMessageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereNumAttachments($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereSubject($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereTo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\DirectMailMessage whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class DirectMailMessage extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CarePlanTemplate.
     *
     * @property int                                                                           $id
     * @property string                                                                        $display_name
     * @property int|null                                                                      $program_id
     * @property string                                                                        $type
     * @property \Carbon\Carbon                                                                $created_at
     * @property \Carbon\Carbon                                                                $updated_at
     * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection       $cpmBiometrics
     * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection       $cpmLifestyles
     * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection $cpmMedicationGroups
     * @property \App\Models\CPM\CpmMisc[]|\Illuminate\Database\Eloquent\Collection            $cpmMiscs
     * @property \App\Models\CPM\CpmProblem[]|\Illuminate\Database\Eloquent\Collection         $cpmProblems
     * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection         $cpmSymptoms
     * @property \CircleLinkHealth\Customer\Entities\Practice|null                             $program
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate query()
     */
    class CarePlanTemplate extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesQuestions.
     *
     * @property int                                                                 $qid
     * @property string                                                              $msg_id
     * @property string|null                                                         $qtype
     * @property string|null                                                         $obs_key
     * @property string|null                                                         $description
     * @property string                                                              $icon
     * @property string                                                              $category
     * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection            $careItems
     * @property mixed                                                               $msg_id_and_obs_key
     * @property \App\Observation[]|\Illuminate\Database\Eloquent\Collection         $observations
     * @property \App\CPRulesQuestionSets[]|\Illuminate\Database\Eloquent\Collection $questionSets
     * @property \App\CPRulesItem[]|\Illuminate\Database\Eloquent\Collection         $rulesItems
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereCategory($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereIcon($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereMsgId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereObsKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereQid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions whereQtype($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestions query()
     */
    class CPRulesQuestions extends \Eloquent
    {
    }
}

namespace App{
    /**
     * These are IDs from third party systems.
     *
     * Example use:
     * XYZ CCD Vendor uses our API to submit CCDs and receive back reports and wants their system's id returned in the
     * response.
     *
     * Class ForeignId
     *
     * @property int                                      $id
     * @property int                                      $user_id
     * @property int|null                                 $location_id
     * @property string                                   $foreign_id
     * @property string                                   $system
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereForeignId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereLocationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereSystem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ForeignId query()
     */
    class ForeignId extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\ObservationMeta.
     *
     * @property int              $id
     * @property int              $obs_id
     * @property int              $comment_id
     * @property string           $message_id
     * @property string           $meta_key
     * @property string           $meta_value
     * @property int              $program_id
     * @property int              $legacy_meta_id
     * @property \Carbon\Carbon   $created_at
     * @property \Carbon\Carbon   $updated_at
     * @property \App\Observation $observationMeta
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereCommentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereLegacyMetaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereMessageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereMetaKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereMetaValue($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereObsId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ObservationMeta query()
     */
    class ObservationMeta extends \Eloquent
    {
    }
}

namespace App\Models{
    /**
     * App\Models\ProblemCode.
     *
     * @property int                     $id
     * @property int                     $problem_id
     * @property string                  $code_system_name
     * @property string|null             $code_system_oid
     * @property string                  $code
     * @property string|null             $name
     * @property \Carbon\Carbon|null     $created_at
     * @property \Carbon\Carbon|null     $updated_at
     * @property string|null             $deleted_at
     * @property \App\Models\CCD\Problem $problem
     * @property \App\ProblemCodeSystem  $system
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\ProblemCode onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCodeSystemName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCodeSystemOid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Models\ProblemCode withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\ProblemCode withoutTrashed()
     * @mixin \Eloquent
     *
     * @property int|null                                                                       $problem_code_system_id
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProblemCode whereProblemCodeSystemId($value)
     */
    class ProblemCode extends \Eloquent
    {
    }
}

namespace App\Models{
    /**
     * App\Models\PatientSignup.
     *
     * @property int                 $id
     * @property string              $name
     * @property string              $phone
     * @property string              $email
     * @property string              $comment
     * @property \Carbon\Carbon|null $created_at
     * @property \Carbon\Carbon|null $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereComment($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup wherePhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientSignup query()
     */
    class PatientSignup extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\LGH{
    /**
     * App\Models\PatientData\LGH\LGHInsurance.
     *
     * @property int                 $id
     * @property int|null            $mrn
     * @property int|null            $fin
     * @property string|null         $primary_insurance
     * @property string|null         $primary_policy_nbr
     * @property string|null         $primary_policy_type
     * @property string|null         $primary_subscriber
     * @property string|null         $primary_relation
     * @property string|null         $secondary_insurance
     * @property string|null         $secondary_policy_nbr
     * @property string|null         $secondary_policy_type
     * @property string|null         $secondary_subscriber
     * @property string|null         $secondary_relation
     * @property string|null         $tertiary_insurance
     * @property string|null         $tertiary_policy_nbr
     * @property string|null         $tertiary_policy_type
     * @property string|null         $tertiary_subscriber
     * @property string|null         $tertiary_relation
     * @property \Carbon\Carbon|null $created_at
     * @property \Carbon\Carbon|null $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereFin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryPolicyNbr($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryPolicyType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimaryRelation($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance wherePrimarySubscriber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryPolicyNbr($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryPolicyType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondaryRelation($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereSecondarySubscriber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryPolicyNbr($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryPolicyType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiaryRelation($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereTertiarySubscriber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHInsurance query()
     */
    class LGHInsurance extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\LGH{
    /**
     * App\Models\PatientData\LGH\LGHProvider.
     *
     * @property string|null         $mrn
     * @property string|null         $last_name
     * @property string|null         $first_name
     * @property string|null         $dob
     * @property string|null         $att_phys
     * @property string|null         $medical_record_type
     * @property int|null            $medical_record_id
     * @property int                 $id
     * @property \Carbon\Carbon|null $created_at
     * @property \Carbon\Carbon|null $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereAttPhys($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereDob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\LGH\LGHProvider query()
     */
    class LGHProvider extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\Rappa{
    /**
     * App\Models\PatientData\Rappa\RappaName.
     *
     * @property int|null    $patient_id
     * @property string|null $email
     * @property string|null $first_name
     * @property string|null $last_name
     * @property string|null $home_phone
     * @property string|null $primary_phone
     * @property string|null $work_phone
     * @property string|null $preferred_contact_method
     * @property string|null $preferred_provider
     * @property string|null $address_1
     * @property string|null $address_2
     * @property string|null $city
     * @property string|null $state
     * @property string|null $zip
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereAddress1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereAddress2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereHomePhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePreferredContactMethod($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePreferredProvider($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName wherePrimaryPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereState($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereWorkPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName whereZip($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaName query()
     */
    class RappaName extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\Rappa{
    /**
     * App\Models\PatientData\Rappa\RappaInsAllergy.
     *
     * @property string|null $patient_name
     * @property int|null    $patient_id
     * @property string|null $last_encounter
     * @property string|null $allergy
     * @property string|null $primary_insurance
     * @property string|null $secondary_insurance
     * @property string|null $provider
     * @property string|null $address_1
     * @property string|null $address_2
     * @property string|null $city
     * @property string|null $zip
     * @property string|null $county
     * @property string|null $home_phone
     * @property string|null $primary_phone
     * @property string|null $preferred_contact_method
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereAddress1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereAddress2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereAllergy($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereCounty($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereHomePhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereLastEncounter($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePatientName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePreferredContactMethod($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePrimaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy wherePrimaryPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereProvider($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereSecondaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy whereZip($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaInsAllergy query()
     */
    class RappaInsAllergy extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\Rappa{
    /**
     * App\Models\PatientData\Rappa\RappaData.
     *
     * @property string|null $last_encounter
     * @property string|null $last_name
     * @property string|null $first_name
     * @property string|null $patient_id
     * @property string|null $note
     * @property string|null $medication
     * @property string|null $condition
     * @property string|null $provider
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereCondition($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereLastEncounter($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereMedication($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereNote($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData whereProvider($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\Rappa\RappaData query()
     */
    class RappaData extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\PhoenixHeart{
    /**
     * App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance.
     *
     * @property string|null         $patient_id
     * @property int|null            $order
     * @property string|null         $name
     * @property string|null         $list_name
     * @property int|null            $processed
     * @property \Carbon\Carbon|null $created_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereListName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereOrder($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance whereProcessed($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance query()
     */
    class PhoenixHeartInsurance extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\PhoenixHeart{
    /**
     * App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem.
     *
     * @property string|null         $patient_id
     * @property string|null         $code
     * @property string|null         $description
     * @property string|null         $start_date
     * @property string|null         $end_date
     * @property string|null         $stop_reason
     * @property int|null            $processed
     * @property \Carbon\Carbon|null $created_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereEndDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereProcessed($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereStartDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem whereStopReason($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem query()
     */
    class PhoenixHeartProblem extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\PhoenixHeart{
    /**
     * App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication.
     *
     * @property string|null         $patient_id
     * @property string|null         $description
     * @property string|null         $instructions
     * @property string|null         $start_date
     * @property string|null         $end_date
     * @property string|null         $stop_reason
     * @property int|null            $processed
     * @property \Carbon\Carbon|null $created_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereEndDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereInstructions($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereProcessed($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereStartDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication whereStopReason($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartMedication query()
     */
    class PhoenixHeartMedication extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\PhoenixHeart{
    /**
     * App\Models\PatientData\PhoenixHeart\PhoenixHeartName.
     *
     * @property string|null $patient_id
     * @property string|null $provider_last_name
     * @property string|null $provider_first_name
     * @property string|null $patient_last_name
     * @property string|null $patient_first_name
     * @property string|null $patient_middle_name
     * @property string|null $dob
     * @property string|null $gender
     * @property string|null $email
     * @property string|null $phone_1_type
     * @property int|null    $phone_1
     * @property string|null $phone_2_type
     * @property string|null $phone_2
     * @property string|null $phone_3_type
     * @property string|null $phone_3
     * @property string|null $address_1
     * @property string|null $address_2
     * @property string|null $city
     * @property string|null $state
     * @property string|null $zip
     * @property int         $processed
     * @property int|null    $eligible
     * @property string|null $created_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereAddress1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereAddress2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereDob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereEligible($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereGender($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePatientFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePatientLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePatientMiddleName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePhone1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePhone1Type($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePhone2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePhone2Type($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePhone3($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName wherePhone3Type($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereProcessed($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereProviderFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereProviderLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereState($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereZip($value)
     * @mixin \Eloquent
     *
     * @property int                                                                            $id
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartName whereId($value)
     */
    class PhoenixHeartName extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\PhoenixHeart{
    /**
     * App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy.
     *
     * @property string|null         $patient_id
     * @property string|null         $name
     * @property string|null         $description
     * @property string|null         $start_date
     * @property string|null         $end_date
     * @property string|null         $stop_reason
     * @property int|null            $processed
     * @property \Carbon\Carbon|null $created_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereEndDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereProcessed($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereStartDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy whereStopReason($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\PhoenixHeart\PhoenixHeartAllergy query()
     */
    class PhoenixHeartAllergy extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\RockyMountain{
    /**
     * App\Models\PatientData\RockyMountain\RockyData.
     *
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyData newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyData newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyData query()
     */
    class RockyData extends \Eloquent
    {
    }
}

namespace App\Models\PatientData\RockyMountain{
    /**
     * App\Models\PatientData\RockyMountain\RockyName.
     *
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyName newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyName newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientData\RockyMountain\RockyName query()
     */
    class RockyName extends \Eloquent
    {
    }
}

namespace App\Models{
    /**
     * App\Models\EmailSettings.
     *
     * @property int                                      $id
     * @property int                                      $user_id
     * @property string                                   $frequency
     * @property \Carbon\Carbon|null                      $created_at
     * @property \Carbon\Carbon|null                      $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereFrequency($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings query()
     */
    class EmailSettings extends \Eloquent
    {
    }
}

namespace App\Models{
    /**
     * App\Models\Addendum.
     *
     * @property int                                           $id
     * @property string                                        $addendumable_type
     * @property int                                           $addendumable_id
     * @property int                                           $author_user_id
     * @property string                                        $body
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $addendumable
     * @property \CircleLinkHealth\Customer\Entities\User      $author
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAddendumableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAddendumableType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereAuthorUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereBody($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Addendum query()
     */
    class Addendum extends \Eloquent
    {
    }
}

namespace App\Models\CCD{
    /**
     * App\Models\CCD\CcdInsurancePolicy.
     *
     * @property int                                           $id
     * @property int|null                                      $medical_record_id
     * @property string|null                                   $medical_record_type
     * @property int|null                                      $patient_id
     * @property string                                        $name
     * @property string|null                                   $type
     * @property string|null                                   $policy_id
     * @property string|null                                   $relation
     * @property string|null                                   $subscriber
     * @property int                                           $approved
     * @property \Carbon\Carbon                                $created_at
     * @property \Carbon\Carbon                                $updated_at
     * @property string|null                                   $deleted_at
     * @property \CircleLinkHealth\Customer\Entities\User|null $patient
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\CcdInsurancePolicy onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereApproved($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy wherePolicyId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereRelation($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereSubscriber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy withMedicalRecord($id, $type = 'App\Models\MedicalRecords\Ccda')
     * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\CcdInsurancePolicy withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\CcdInsurancePolicy withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdInsurancePolicy query()
     */
    class CcdInsurancePolicy extends \Eloquent
    {
    }
}

namespace App\Models\CCD{
    /**
     * App\Models\CCD\Allergy.
     *
     * @property int                                      $id
     * @property int|null                                 $allergy_import_id
     * @property int|null                                 $ccda_id
     * @property int                                      $patient_id
     * @property int|null                                 $vendor_id
     * @property int|null                                 $ccd_allergy_log_id
     * @property string|null                              $allergen_name
     * @property string|null                              $deleted_at
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \App\Importer\Models\ItemLogs\AllergyLog $ccdLog
     * @property \CircleLinkHealth\Customer\Entities\User $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereAllergenName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereAllergyImportId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCcdAllergyLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCcdaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy whereVendorId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Allergy query()
     */
    class Allergy extends \Eloquent
    {
    }
}

namespace App\Models\CCD{
    /**
     * App\Models\CCD\Problem.
     *
     * @property int                                                                $id
     * @property int|null                                                           $problem_import_id
     * @property int|null                                                           $ccda_id
     * @property int                                                                $patient_id
     * @property int|null                                                           $ccd_problem_log_id
     * @property string|null                                                        $name
     * @property string|null                                                        $original_name
     * @property int|null                                                           $cpm_problem_id
     * @property int|null                                                           $cpm_instruction_id
     * @property string|null                                                        $deleted_at
     * @property \Carbon\Carbon                                                     $created_at
     * @property \Carbon\Carbon                                                     $updated_at
     * @property \App\Importer\Models\ItemLogs\ProblemLog|null                      $ccdLog
     * @property \App\Models\ProblemCode[]|\Illuminate\Database\Eloquent\Collection $codes
     * @property \App\Models\CPM\CpmProblem|null                                    $cpmProblem
     * @property \CircleLinkHealth\Customer\Entities\User                           $patient
     * @property \App\Models\CPM\CpmInstruction                                     $cpmInstruction
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereActivate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCcdProblemLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCcdaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCodeSystem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCodeSystemName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCpmProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereIcd10Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereProblemImportId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereVendorId($value)
     * @mixin \Eloquent
     *
     * @property int                                                                                                  $is_monitored     A monitored problem is a problem we provide Care Management for.
     * @property int|null                                                                                             $billable
     * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $patientSummaries
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                       $revisionHistory
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem newQuery()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\Problem onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem query()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereBillable($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Problem whereIsMonitored($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\Problem withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\CCD\Problem withoutTrashed()
     */
    class Problem extends \Eloquent
    {
    }
}

namespace App\Models\CCD{
    /**
     * App\Models\CCD\Medication.
     *
     * @property int                                         $id
     * @property int|null                                    $medication_import_id
     * @property int|null                                    $ccda_id
     * @property int                                         $patient_id
     * @property int|null                                    $vendor_id
     * @property int|null                                    $ccd_medication_log_id
     * @property int|null                                    $medication_group_id
     * @property string|null                                 $name
     * @property string|null                                 $sig
     * @property string|null                                 $code
     * @property string|null                                 $code_system
     * @property string|null                                 $code_system_name
     * @property string|null                                 $deleted_at
     * @property \Carbon\Carbon                              $created_at
     * @property \Carbon\Carbon                              $updated_at
     * @property \App\Importer\Models\ItemLogs\MedicationLog $ccdLog
     * @property \App\Models\CPM\CpmMedicationGroup          $cpmMedicationGroup
     * @property \CircleLinkHealth\Customer\Entities\User    $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCcdMedicationLogId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCcdaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCodeSystem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCodeSystemName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereMedicationGroupId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereMedicationImportId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereSig($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication whereVendorId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\Medication query()
     */
    class Medication extends \Eloquent
    {
    }
}

namespace App\Models\CCD{
    /**
     * App\Models\CCD\CcdVendor.
     *
     * @property int                                                                                              $id
     * @property int|null                                                                                         $program_id
     * @property int                                                                                              $ccd_import_routine_id
     * @property string                                                                                           $vendor_name
     * @property string|null                                                                                      $ehr_name
     * @property string|null                                                                                      $practice_id
     * @property int|null                                                                                         $ehr_oid
     * @property string|null                                                                                      $doctor_name
     * @property int|null                                                                                         $doctor_oid
     * @property string|null                                                                                      $custodian_name
     * @property \Carbon\Carbon                                                                                   $created_at
     * @property \Carbon\Carbon                                                                                   $updated_at
     * @property \App\Importer\Models\ItemLogs\AllergyLog[]|\Illuminate\Database\Eloquent\Collection              $allergies
     * @property \App\Importer\Models\ItemLogs\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection         $demographics
     * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection $demographicsImports
     * @property \App\Importer\Models\ItemLogs\DocumentLog[]|\Illuminate\Database\Eloquent\Collection             $document
     * @property \App\Importer\Models\ItemLogs\MedicationLog[]|\Illuminate\Database\Eloquent\Collection           $medications
     * @property \App\Importer\Models\ItemLogs\ProblemLog[]|\Illuminate\Database\Eloquent\Collection              $problems
     * @property \App\Importer\Models\ItemLogs\ProviderLog[]|\Illuminate\Database\Eloquent\Collection             $providers
     * @property \App\CLH\CCD\ImportRoutine\CcdImportRoutine                                                      $routine
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereCcdImportRoutineId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereCustodianName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereDoctorName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereDoctorOid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereEhrName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereEhrOid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor whereVendorName($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CCD\CcdVendor query()
     */
    class CcdVendor extends \Eloquent
    {
    }
}

namespace App\Models\MedicalRecords{
    /**
     * App\Models\MedicalRecords\ImportedMedicalRecord.
     *
     * @property int                                                                                            $id
     * @property int|null                                                                                       $patient_id
     * @property string                                                                                         $medical_record_type
     * @property int                                                                                            $medical_record_id
     * @property int|null                                                                                       $billing_provider_id
     * @property int|null                                                                                       $location_id
     * @property int|null                                                                                       $practice_id
     * @property int|null                                                                                       $duplicate_id
     * @property \Carbon\Carbon|null                                                                            $created_at
     * @property \Carbon\Carbon|null                                                                            $updated_at
     * @property string|null                                                                                    $deleted_at
     * @property \App\Importer\Models\ImportedItems\AllergyImport[]|\Illuminate\Database\Eloquent\Collection    $allergies
     * @property \CircleLinkHealth\Customer\Entities\User|null                                                  $billingProvider
     * @property \App\Importer\Models\ImportedItems\DemographicsImport                                          $demographics
     * @property \CircleLinkHealth\Customer\Entities\Location|null                                              $location
     * @property \App\Importer\Models\ImportedItems\MedicationImport[]|\Illuminate\Database\Eloquent\Collection $medications
     * @property \CircleLinkHealth\Customer\Entities\Practice|null                                              $practice
     * @property \App\Importer\Models\ImportedItems\ProblemImport[]|\Illuminate\Database\Eloquent\Collection    $problems
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereBillingProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereLocationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withMedicalRecord($id, $type = 'App\Models\MedicalRecords\Ccda')
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withoutTrashed()
     * @mixin \Eloquent
     *
     * @property array|null                                                                     $validation_checks
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereDuplicateId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereValidationChecks($value)
     */
    class ImportedMedicalRecord extends \Eloquent
    {
    }
}

namespace App\Models\MedicalRecords{
    /**
     * App\Models\MedicalRecords\TabularMedicalRecord.
     *
     * @property int                                                                                              $id
     * @property int|null                                                                                         $practice_id
     * @property int|null                                                                                         $location_id
     * @property int|null                                                                                         $billing_provider_id
     * @property int|null                                                                                         $uploaded_by
     * @property int|null                                                                                         $patient_id
     * @property string|null                                                                                      $patient_name
     * @property string|null                                                                                      $first_name
     * @property string|null                                                                                      $last_name
     * @property \Carbon\Carbon|null                                                                              $dob
     * @property string|null                                                                                      $problems_string
     * @property string|null                                                                                      $medications_string
     * @property string|null                                                                                      $allergies_string
     * @property string|null                                                                                      $provider_name
     * @property string|null                                                                                      $mrn
     * @property string|null                                                                                      $gender
     * @property string|null                                                                                      $language
     * @property \Carbon\Carbon                                                                                   $consent_date
     * @property string|null                                                                                      $primary_phone
     * @property string|null                                                                                      $cell_phone
     * @property string|null                                                                                      $home_phone
     * @property string|null                                                                                      $work_phone
     * @property string|null                                                                                      $email
     * @property string|null                                                                                      $address
     * @property string|null                                                                                      $address2
     * @property string|null                                                                                      $city
     * @property string|null                                                                                      $state
     * @property string|null                                                                                      $zip
     * @property string|null                                                                                      $primary_insurance
     * @property string|null                                                                                      $secondary_insurance
     * @property string|null                                                                                      $tertiary_insurance
     * @property \Carbon\Carbon|null                                                                              $created_at
     * @property \Carbon\Carbon|null                                                                              $updated_at
     * @property string|null                                                                                      $preferred_call_times
     * @property string|null                                                                                      $preferred_call_days
     * @property \App\Importer\Models\ItemLogs\AllergyLog[]|\Illuminate\Database\Eloquent\Collection              $allergies
     * @property \App\Importer\Models\ItemLogs\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection         $demographics
     * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection $demographicsImports
     * @property \App\Importer\Models\ItemLogs\DocumentLog[]|\Illuminate\Database\Eloquent\Collection             $document
     * @property \App\Importer\Models\ItemLogs\MedicationLog[]|\Illuminate\Database\Eloquent\Collection           $medications
     * @property \App\Importer\Models\ItemLogs\ProblemLog[]|\Illuminate\Database\Eloquent\Collection              $problems
     * @property \App\Importer\Models\ItemLogs\ProviderLog[]|\Illuminate\Database\Eloquent\Collection             $providers
     *
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
     *
     * @property string|null                                                                    $deleted_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord newQuery()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\TabularMedicalRecord onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord query()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\TabularMedicalRecord whereDeletedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\TabularMedicalRecord withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\TabularMedicalRecord withoutTrashed()
     */
    class TabularMedicalRecord extends \Eloquent
    {
    }
}

namespace App\Models\MedicalRecords{
    /**
     * App\Models\MedicalRecords\Ccda.
     *
     * @property int                                                                                 $id
     * @property \Carbon\Carbon|null                                                                 $date
     * @property string|null                                                                         $mrn
     * @property string|null                                                                         $referring_provider_name
     * @property int|null                                                                            $location_id
     * @property int|null                                                                            $practice_id
     * @property int|null                                                                            $billing_provider_id
     * @property int|null                                                                            $user_id
     * @property int|null                                                                            $patient_id
     * @property int                                                                                 $vendor_id
     * @property string                                                                              $source
     * @property int                                                                                 $imported
     * @property string                                                                              $xml
     * @property string|null                                                                         $json
     * @property string|null                                                                         $status
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property string|null                                                                         $deleted_at
     * @property \App\Importer\Models\ItemLogs\AllergyLog[]|\Illuminate\Database\Eloquent\Collection $allergies
     * @property \App\Entities\CcdaRequest                                                           $ccdaRequest
     * @property \App\Importer\Models\ItemLogs\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection
     *     $demographics
     * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection
     *     $demographicsImports
     * @property \App\Importer\Models\ItemLogs\DocumentLog[]|\Illuminate\Database\Eloquent\Collection   $document
     * @property \App\Importer\Models\ItemLogs\MedicationLog[]|\Illuminate\Database\Eloquent\Collection $medications
     * @property \CircleLinkHealth\Customer\Entities\User|null                                          $patient
     * @property \App\Importer\Models\ItemLogs\ProblemLog[]|\Illuminate\Database\Eloquent\Collection    $problems
     * @property \App\Importer\Models\ItemLogs\ProviderLog[]|\Illuminate\Database\Eloquent\Collection   $providers
     * @property \App\Models\MedicalRecords\ImportedMedicalRecord                                       $qaSummary
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereBillingProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereImported($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereJson($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereLocationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda
     *     whereReferringProviderName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereSource($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereVendorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereXml($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda withoutTrashed()
     * @mixin \Eloquent
     *
     * @property int|null                                                                       $direct_mail_message_id
     * @property int|null                                                                       $batch_id
     * @property \App\DirectMailMessage                                                         $directMessage
     * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                          $media
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda exclude($value = array())
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereBatchId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereDirectMailMessageId($value)
     */
    class Ccda extends \Eloquent
    {
    }
}

namespace App\Models\CPM\Biometrics{
    /**
     * App\Models\CPM\Biometrics\CpmBloodPressure.
     *
     * @property int                                      $id
     * @property int                                      $patient_id
     * @property string                                   $starting
     * @property string                                   $target
     * @property string                                   $systolic_high_alert
     * @property string                                   $systolic_low_alert
     * @property string                                   $diastolic_high_alert
     * @property string                                   $diastolic_low_alert
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereDiastolicHighAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereDiastolicLowAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereStarting($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereSystolicHighAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereSystolicLowAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereTarget($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure query()
     */
    class CpmBloodPressure extends \Eloquent
    {
    }
}

namespace App\Models\CPM\Biometrics{
    /**
     * App\Models\CPM\Biometrics\CpmSmoking.
     *
     * @property int                                      $id
     * @property int                                      $patient_id
     * @property string                                   $starting
     * @property string                                   $target
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereStarting($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereTarget($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking query()
     */
    class CpmSmoking extends \Eloquent
    {
    }
}

namespace App\Models\CPM\Biometrics{
    /**
     * App\Models\CPM\Biometrics\CpmWeight.
     *
     * @property int                                      $id
     * @property int                                      $patient_id
     * @property string                                   $starting
     * @property string                                   $target
     * @property int                                      $monitor_changes_for_chf
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereMonitorChangesForChf($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereStarting($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereTarget($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight query()
     */
    class CpmWeight extends \Eloquent
    {
    }
}

namespace App\Models\CPM\Biometrics{
    /**
     * App\Models\CPM\Biometrics\CpmBloodSugar.
     *
     * @property int                                      $id
     * @property int                                      $patient_id
     * @property string                                   $starting
     * @property string                                   $target
     * @property string                                   $starting_a1c
     * @property string                                   $high_alert
     * @property string                                   $low_alert
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereHighAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereLowAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereStarting($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereStartingA1c($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereTarget($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar query()
     */
    class CpmBloodSugar extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmSymptom.
     *
     * @property int                                                                                 $id
     * @property int|null                                                                            $care_item_id
     * @property string                                                                              $name
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                    $carePlanTemplates
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $patient
     * @property \App\Models\CPM\CpmSymptomUser[]|\Illuminate\Database\Eloquent\Collection           $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom query()
     */
    class CpmSymptom extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmMiscUser.
     *
     * @property int                                      $id
     * @property int|null                                 $cpm_instruction_id
     * @property int                                      $patient_id
     * @property int                                      $cpm_misc_is
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \App\Models\CPM\CpmInstruction           $cpmInstruction
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property int                                                                            $cpm_misc_id
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection      $cpmInstructions
     * @property \App\Models\CPM\CpmMisc                                                        $cpmMisc
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser whereCpmMiscId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMiscUser wherePatientId($value)
     */
    class CpmMiscUser extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmLifestyle.
     *
     * @property int                                                                                 $id
     * @property int|null                                                                            $care_item_id
     * @property string                                                                              $name
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                    $carePlanTemplates
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $patient
     * @property \App\Models\CPM\CpmLifestyleUser[]|\Illuminate\Database\Eloquent\Collection         $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle query()
     */
    class CpmLifestyle extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmLifestyleUser.
     *
     * @property int                                      $id
     * @property int|null                                 $cpm_instruction_id
     * @property int                                      $patient_id
     * @property int                                      $cpm_lifestyle_id
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property App\Models\CPM\CpmInstruction            $cpmInstruction
     * @property App\Models\CPM\CpmLifestyle              $cpmLifestyle
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection      $cpmInstructions
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser whereCpmLifestyleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyleUser wherePatientId($value)
     */
    class CpmLifestyleUser extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmInstruction.
     *
     * @property int            $id
     * @property int            $is_default
     * @property string         $name
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereIsDefault($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection        $cpmBiometrics
     * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection        $cpmLifestyles
     * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection  $cpmMedicationGroups
     * @property \App\Models\CPM\CpmMisc[]|\Illuminate\Database\Eloquent\Collection             $cpmMisc
     * @property \App\Models\CPM\CpmProblem[]|\Illuminate\Database\Eloquent\Collection          $cpmProblems
     * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection          $cpmSymptom
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstruction query()
     */
    class CpmInstruction extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmMedicationGroup.
     *
     * @property int                                                                                 $id
     * @property int|null                                                                            $care_item_id
     * @property string                                                                              $name
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                    $carePlanTemplates
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
     * @property \App\Models\CCD\Medication[]|\Illuminate\Database\Eloquent\Collection               $medications
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMedicationGroup query()
     */
    class CpmMedicationGroup extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmBiometric.
     *
     * @property int                                         $id
     * @property int|null                                    $cpm_instruction_id
     * @property int                                         $cpm_biometric_id
     * @property int                                         $patient_id
     * @property \Carbon\Carbon                              $created_at
     * @property \Carbon\Carbon                              $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User    $patient
     * @property \App\Models\CPM\CpmBiometric                $biometric
     * @property \App\Models\CPM\CpmInstruction              $instruction
     * @property \App\Models\CPM\Biometrics\CpmBloodPressure $bloodPressure
     * @property \App\Models\CPM\Biometrics\CpmBloodSugar    $bloodSugar
     * @property \App\Models\CPM\Biometrics\CpmBloodSmoking  $smoking
     * @property \App\Models\CPM\Biometrics\CpmBloodWeight   $weight
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser whereCpmBiometricId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser wherePatientId($value)
     */
    class CpmBiometricUser extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmProblem.
     *
     * @property int                            $id
     * @property int                            $cpm_instruction_id
     * @property int                            $patient_id
     * @property int                            $cpm_problem_id
     * @property \Carbon\Carbon                 $created_at
     * @property \Carbon\Carbon                 $updated_at
     * @property \App\Models\CPM\CpmInstruction $instruction
     * @mixin \Eloquent
     *
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection      $cpmInstructions
     * @property \App\Models\CPM\CpmProblem                                                     $problems
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereCpmProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblemUser whereUpdatedAt($value)
     */
    class CpmProblemUser extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmProblem.
     *
     * @property int                                                                                 $id
     * @property string                                                                              $default_icd_10_code
     * @property string                                                                              $name
     * @property string                                                                              $icd10from
     * @property string                                                                              $icd10to
     * @property float                                                                               $icd9from
     * @property float                                                                               $icd9to
     * @property string                                                                              $contains
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                    $carePlanTemplates
     * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection             $cpmBiometricsToBeActivated
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
     * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection             $cpmLifestylesToBeActivated
     * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection       $cpmMedicationGroupsToBeActivated
     * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection               $cpmSymptomsToBeActivated
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $patient
     * @property App\Models\CPM\CpmInstructable                                                      $instructable
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereContains($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereDefaultIcd10Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd10from($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd10to($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd9from($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd9to($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property int                                                                                         $is_behavioral
     * @property int                                                                                         $weight
     * @property \App\Importer\Models\ImportedItems\ProblemImport[]|\Illuminate\Database\Eloquent\Collection $problemImports
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]              $revisionHistory
     * @property \App\CLH\CCD\Importer\SnomedToCpmIcdMap[]|\Illuminate\Database\Eloquent\Collection          $snomedMaps
     * @property \App\Models\CPM\CpmProblemUser[]|\Illuminate\Database\Eloquent\Collection                   $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIsBehavioral($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereWeight($value)
     */
    class CpmProblem extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmSymptomUser.
     *
     * @property int                                                                                 $id
     * @property int                                                                                 $cpm_symptom_id
     * @property int|null                                                                            $cpm_instruction_id
     * @property int|null                                                                            $patient_id
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property \App\Models\CPM\CpmSymptom                                                          $cpmSymptom
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \App\Models\CPM\CpmInstruction                                                 $instruction
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCpmSymptomId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser wherePatientId($value)
     */
    class CpmSymptomUser extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmBiometric.
     *
     * @property int                                                                                                                                                             $id
     * @property int|null                                                                                                                                                        $care_item_id
     * @property string                                                                                                                                                          $name
     * @property int|null                                                                                                                                                        $type
     * @property string                                                                                                                                                          $unit
     * @property \Carbon\Carbon                                                                                                                                                  $created_at
     * @property \Carbon\Carbon                                                                                                                                                  $updated_at
     * @property \App\Models\CPM\Biometrics\CpmBloodPressure|\App\Models\CPM\Biometrics\CpmBloodSugar|\App\Models\CPM\Biometrics\CpmSmoking|\App\Models\CPM\Biometrics\CpmWeight $info
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                                                                                                $carePlanTemplates
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection                                                                                       $cpmInstructions
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                                                                             $patient
     * @property \App\Models\CPM\CpmBiometricUser[]|\Illuminate\Database\Eloquent\Collection                                                                                     $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUnit($value)
     */
    class CpmBiometric extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmMisc.
     *
     * @property int                                                                                 $id
     * @property int|null                                                                            $details_care_item_id
     * @property int|null                                                                            $care_item_id
     * @property string                                                                              $name
     * @property \Illuminate\Support\Carbon                                                          $created_at
     * @property \Illuminate\Support\Carbon                                                          $updated_at
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                    $carePlanTemplates
     * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $patient
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]      $revisionHistory
     * @property \App\Models\CPM\CpmMiscUser[]|\Illuminate\Database\Eloquent\Collection              $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereDetailsCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class CpmMisc extends \Eloquent
    {
    }
}

namespace App\Models\CPM{
    /**
     * App\Models\CPM\CpmMiscUser.
     *
     * @property int                            $id
     * @property int|null                       $cpm_instruction_id
     * @property int                            $instructable_id
     * @property int                            $instruction_type
     * @property \Carbon\Carbon                 $created_at
     * @property \Carbon\Carbon                 $updated_at
     * @property \App\Models\CPM\CpmInstruction $cpmInstruction
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property string                                                                         $instructable_type
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable whereCpmInstructionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable whereInstructableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmInstructable whereInstructableType($value)
     */
    class CpmInstructable extends \Eloquent
    {
    }
}

namespace App\Models{
    /**
     * App\Models\Pdf.
     *
     * @property int                                           $id
     * @property string                                        $pdfable_type
     * @property int                                           $pdfable_id
     * @property string                                        $filename
     * @property int|null                                      $uploaded_by
     * @property string                                        $file
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property string|null                                   $deleted_at
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $pdfable
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\Pdf onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereFile($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereFilename($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf wherePdfableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf wherePdfableType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf whereUploadedBy($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Models\Pdf withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\Pdf withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pdf query()
     */
    class Pdf extends \Eloquent
    {
    }
}

namespace App{
    /**
     * Represents a twilio voice recording.
     *
     * @property string                                                                         $call_sid
     * @property string                                                                         $account_sid
     * @property string                                                                         $conference_sid  Only present in case of a recording of a conference
     * @property string                                                                         $source          One of 'DialVerb' and 'Conference'
     * @property string                                                                         $status          One of 'in-progress' and 'completed'
     * @property string                                                                         $url
     * @property int                                                                            $duration
     * @property int                                                                            $id
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereAccountSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereCallSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereConferenceSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereSource($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioRecording whereUrl($value)
     * @mixin \Eloquent
     */
    class TwilioRecording extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CareItem.
     *
     * @property int                                                      $id
     * @property string|null                                              $model_field_name
     * @property int|null                                                 $type_id
     * @property string|null                                              $type
     * @property string                                                   $relationship_fn_name
     * @property int                                                      $parent_id
     * @property int                                                      $qid
     * @property string                                                   $obs_key
     * @property string                                                   $name
     * @property string                                                   $display_name
     * @property string                                                   $description
     * @property \Carbon\Carbon                                           $created_at
     * @property \Carbon\Carbon                                           $updated_at
     * @property \App\CarePlan[]|\Illuminate\Database\Eloquent\Collection $carePlans
     * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection $children
     * @property mixed                                                    $meta_key
     * @property \App\CareItem                                            $parents
     * @property \App\CPRulesQuestions                                    $question
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereModelFieldName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereObsKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereParentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereQid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereRelationshipFnName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereTypeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem query()
     */
    class CareItem extends \Eloquent
    {
    }
}

namespace App{
    /**
     * Structured twilio call logs.
     *
     * @property string                                                                         $call_sid                 Unique Identifier for a call session (note: a call session can have dial leg or
     *                                                                                                                    conference leg, which have different ids)
     * @property string                                                                         $call_status              One of: queued, ringing, in-progress, completed (status about the call session
     *                                                                                                                    itself. To see if the call was answered or not, use dial_call_status)
     * @property string                                                                         $from                     The phone number, SIP address, Client identifier or SIM SID that made this Call
     * @property string                                                                         $to                       The target call
     * @property string                                                                         $inbound_user_id          The user receiving the call
     * @property string                                                                         $outbound_user_id         The user making the call
     * @property int                                                                            $call_duration            The total duration from the moment you press Call on the web site until the
     *                                                                                                                    connection is closed.
     * @property string                                                                         $direction                inbound for inbound calls, outbound-api for calls initiated via the REST API or
     *                                                                                                                    outbound-dial for calls initiated by a <Dial> verb.
     * @property bool                                                                           $in_conference            States whether the call is in conference mode
     * @property bool                                                                           $is_unlisted_number       States whether the phone number was manually entered on client side
     * @property int                                                                            $dial_conference_duration The effective duration of a call from the moment the first participant
     *                                                                                                                    answers until close.
     * @property int                                                                            $dial_call_status         Read this value to see if the other party has picked up (queued, ringing,
     *                                                                                                                    in-progress, completed, busy, failed, no-answer)
     * @property int                                                                            $dial_call_sid            The session id of the call to the other party. Different from call_sid.
     *                                                                                                                    call_sid is the Parent session. connection is closed.
     * @property string                                                                         $dial_recording_sid
     * @property string                                                                         $conference_sid
     * @property int                                                                            $conference_duration
     * @property int                                                                            $conference_status
     * @property string                                                                         $conference_recording_sid
     * @property string                                                                         $conference_friendly_name
     * @property int                                                                            $id
     * @property string|null                                                                    $application_sid
     * @property string|null                                                                    $account_sid
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereAccountSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereApplicationSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereCallDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereCallSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereCallStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereConferenceFriendlyName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereConferenceRecordingSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereConferenceSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereConferenceStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereDialCallSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereDialCallStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereDialConferenceDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereDialRecordingSid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereDirection($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereFrom($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereInConference($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereInboundUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereIsUnlistedNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereOutboundUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereTo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TwilioCall whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class TwilioCall extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\EnrolleeView.
     *
     * @property int                             $id
     * @property int|null                        $batch_id
     * @property int|null                        $eligibility_job_id
     * @property string|null                     $medical_record_type
     * @property int|null                        $medical_record_id
     * @property int|null                        $user_id
     * @property int|null                        $provider_id
     * @property int|null                        $practice_id
     * @property int|null                        $care_ambassador_user_id
     * @property int                             $total_time_spent
     * @property string|null                     $last_call_outcome
     * @property string|null                     $last_call_outcome_reason
     * @property string|null                     $mrn
     * @property string                          $first_name
     * @property string                          $last_name
     * @property string|null                     $address
     * @property string|null                     $address_2
     * @property string|null                     $city
     * @property string|null                     $state
     * @property string|null                     $zip
     * @property string|null                     $primary_phone
     * @property string|null                     $other_phone
     * @property string|null                     $home_phone
     * @property string|null                     $cell_phone
     * @property string|null                     $dob
     * @property string|null                     $lang
     * @property string|null                     $invite_code
     * @property string|null                     $status
     * @property int|null                        $attempt_count
     * @property string|null                     $preferred_days
     * @property string|null                     $preferred_window
     * @property string|null                     $invite_sent_at
     * @property string|null                     $consented_at
     * @property string|null                     $last_attempt_at
     * @property string|null                     $invite_opened_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null                     $primary_insurance
     * @property string|null                     $secondary_insurance
     * @property string|null                     $tertiary_insurance
     * @property int|null                        $has_copay
     * @property string|null                     $email
     * @property string|null                     $last_encounter
     * @property string                          $referring_provider_name
     * @property int|null                        $confident_provider_guess
     * @property string                          $problems
     * @property int                             $cpm_problem_1
     * @property int|null                        $cpm_problem_2
     * @property string|null                     $color
     * @property string|null                     $soft_rejected_callback
     * @property string|null                     $provider_name
     * @property string|null                     $care_ambassador_name
     * @property string|null                     $practice_name
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAddress($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAddress2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAttemptCount($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereBatchId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCareAmbassadorName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCareAmbassadorUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCellPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereColor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereConfidentProviderGuess($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereConsentedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCpmProblem1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCpmProblem2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereDob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereEligibilityJobId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereHasCopay($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereHomePhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereInviteCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereInviteOpenedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereInviteSentAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLang($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastAttemptAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastCallOutcome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastCallOutcomeReason($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastEncounter($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereOtherPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePracticeName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePreferredDays($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePreferredWindow($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePrimaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePrimaryPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProblems($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProviderName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereReferringProviderName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereSecondaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereSoftRejectedCallback($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereState($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereTertiaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereTotalTimeSpent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereZip($value)
     * @mixin \Eloquent
     */
    class EnrolleeView extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\ProcessedFile.
     *
     * @property int                 $id
     * @property string              $path
     * @property \Carbon\Carbon|null $created_at
     * @property \Carbon\Carbon|null $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile wherePath($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProcessedFile query()
     */
    class ProcessedFile extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesPCP.
     *
     * @property int                                                                                     $pcp_id
     * @property int|null                                                                                $prov_id
     * @property string|null                                                                             $section_text
     * @property string|null                                                                             $status
     * @property int|null                                                                                $cpset_id
     * @property string|null                                                                             $pcp_type
     * @property \App\CPRulesItem[]|\Illuminate\Database\Eloquent\Collection                             $items
     * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $program
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereCpsetId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP wherePcpId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP wherePcpType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereProvId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereSectionText($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereStatus($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP query()
     */
    class CPRulesPCP extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CareAmbassadorLog.
     *
     * @property int                      $id
     * @property int|null                 $enroller_id
     * @property string                   $day
     * @property int                      $no_enrolled
     * @property int                      $no_rejected
     * @property int                      $no_soft_rejected
     * @property int                      $no_utc
     * @property int                      $total_calls
     * @property int                      $total_time_in_system
     * @property \Carbon\Carbon|null      $created_at
     * @property \Carbon\Carbon|null      $updated_at
     * @property \App\CareAmbassador|null $enroller
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereDay($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereEnrollerId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoEnrolled($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoRejected($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoUtc($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereTotalCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereTotalTimeInSystem($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property int|null                                                                       $practice_id
     * @property \CircleLinkHealth\Customer\Entities\Practice                                   $practice
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoSoftRejected($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog wherePracticeId($value)
     */
    class CareAmbassadorLog extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CareSection.
     *
     * @property int                                                          $id
     * @property string                                                       $name
     * @property string                                                       $display_name
     * @property string                                                       $description
     * @property string                                                       $template
     * @property \Carbon\Carbon                                               $created_at
     * @property \Carbon\Carbon                                               $updated_at
     * @property \App\CarePlanItem[]|\Illuminate\Database\Eloquent\Collection $carePlanItems
     * @property \App\CarePlan[]|\Illuminate\Database\Eloquent\Collection     $carePlans
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereTemplate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CareSection query()
     */
    class CareSection extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Comment.
     *
     * @property int                                      $id
     * @property int                                      $comment_post_ID
     * @property string                                   $comment_author
     * @property string                                   $comment_author_email
     * @property string                                   $comment_author_url
     * @property string                                   $comment_author_IP
     * @property \Carbon\Carbon                           $comment_date
     * @property \Carbon\Carbon                           $comment_date_gmt
     * @property string                                   $comment_content
     * @property int                                      $comment_karma
     * @property string                                   $comment_approved
     * @property string                                   $comment_agent
     * @property string                                   $comment_type
     * @property int                                      $comment_parent
     * @property int                                      $user_id
     * @property int                                      $program_id
     * @property int                                      $legacy_comment_id
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \App\Observation                         $observation
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAgent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentApproved($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthorEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthorIP($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentAuthorUrl($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentContent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentDateGmt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentKarma($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentParent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentPostID($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCommentType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereLegacyCommentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment query()
     */
    class Comment extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\EligibilityJob.
     *
     * @property int                                                                            $id
     * @property int                                                                            $batch_id
     * @property string|null                                                                    $hash
     * @property int|null                                                                       $status
     * @property array                                                                          $data
     * @property string|null                                                                    $outcome
     * @property string|null                                                                    $reason
     * @property array                                                                          $messages
     * @property array|null                                                                     $errors
     * @property \Illuminate\Support\Carbon|null                                                $last_encounter
     * @property string|null                                                                    $primary_insurance
     * @property string|null                                                                    $secondary_insurance
     * @property string|null                                                                    $tertiary_insurance
     * @property int|null                                                                       $ccm_problem_1_id
     * @property int|null                                                                       $ccm_problem_2_id
     * @property int|null                                                                       $bhi_problem_id
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property string|null                                                                    $deleted_at
     * @property int                                                                            $invalid_data
     * @property int                                                                            $invalid_structure
     * @property int                                                                            $invalid_mrn
     * @property int                                                                            $invalid_first_name
     * @property int                                                                            $invalid_last_name
     * @property int                                                                            $invalid_dob
     * @property int                                                                            $invalid_problems
     * @property int                                                                            $invalid_phones
     * @property \App\EligibilityBatch                                                          $batch
     * @property \App\Enrollee                                                                  $enrollee
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob eligible()
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob newQuery()
     * @method static \Illuminate\Database\Query\Builder|\App\EligibilityJob onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob query()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereBatchId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereBhiProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereCcmProblem1Id($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereCcmProblem2Id($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereErrors($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereHash($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidData($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidDob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidPhones($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidProblems($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidStructure($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereLastEncounter($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereMessages($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereOutcome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob wherePrimaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereReason($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereSecondaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereTertiaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\EligibilityJob withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\EligibilityJob withoutTrashed()
     * @mixin \Eloquent
     */
    class EligibilityJob extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CallView.
     *
     * @property int            $id
     * @property \Carbon\Carbon $call_time_start
     * @property \Carbon\Carbon $call_time_end
     * @property \Carbon\Carbon $patient_created_at
     * @property string preferred_call_days
     * @property int|null no_call_attempts_since_last_success
     * @property int|null    $is_manual
     * @property string      $status
     * @property string|null $type
     * @property int|null    $nurse_id
     * @property string|null $nurse
     * @property int|null    $patient_id
     * @property string|null $patient
     * @property string|null $scheduled_date
     * @property string|null $last_call
     * @property int|null    $ccm_time
     * @property int|null    $bhi_time
     * @property int|null    $no_of_calls
     * @property int|null    $no_of_successful_calls
     * @property int|null    $practice_id
     * @property string|null $practice
     * @property string|null $timezone
     * @property string|null $preferred_call_days
     * @property int         $is_ccm
     * @property int         $is_bhi
     * @property string|null $scheduler
     * @property string|null $billing_provider
     * @property string|null $attempt_note
     * @property string|null $general_comment
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereAttemptNote($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereBhiTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereBillingProvider($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereCallTimeEnd($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereCallTimeStart($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereCcmTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereGeneralComment($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereIsBhi($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereIsCcm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereIsManual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereLastCall($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereNoOfCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereNoOfSuccessfulCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereNurse($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereNurseId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView wherePatient($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView wherePractice($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView wherePreferredCallDays($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereScheduledDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereScheduler($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereTimezone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CallView whereType($value)
     * @mixin \Eloquent
     */
    class CallView extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CarePlanItem.
     *
     * @property \App\CareItem                                                $careItem
     * @property \App\CarePlan                                                $carePlan
     * @property \App\CareSection                                             $careSection
     * @property \App\CarePlanItem[]|\Illuminate\Database\Eloquent\Collection $children
     * @property \App\CarePlanItem                                            $parents
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem query()
     */
    class CarePlanItem extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\TargetPatient.
     *
     * @property int                                                                            $id
     * @property int|null                                                                       $batch_id
     * @property string|null                                                                    $eligibility_job_id
     * @property int                                                                            $ehr_id
     * @property int|null                                                                       $user_id
     * @property int|null                                                                       $enrollee_id
     * @property int                                                                            $ehr_patient_id
     * @property int                                                                            $ehr_practice_id
     * @property int                                                                            $ehr_department_id
     * @property string|null                                                                    $status
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property string                                                                         $description
     * @property \CircleLinkHealth\Customer\Entities\Ehr                                        $ehr
     * @property \App\Enrollee|null                                                             $enrollee
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\User|null                                  $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereBatchId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrDepartmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrPatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrPracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEligibilityJobId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEnrolleeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereUserId($value)
     * @mixin \Eloquent
     */
    class TargetPatient extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Call.
     *
     * @property int            $id
     * @property int|null       $note_id
     * @property string|null    $type
     * @property string|null    $sub_type
     * @property string         $service
     * @property string         $status
     * @property string         $inbound_phone_number
     * @property string         $outbound_phone_number
     * @property int            $inbound_cpm_id
     * @property int|null       $outbound_cpm_id
     * @property int|null       $call_time
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     * @property int            $is_cpm_outbound
     * @property string         $window_start
     * @property string         $window_end
     * @property string         $scheduled_date
     * @property string|null    $called_date
     * @property string         $attempt_note
     * @property string|null    $scheduler
     * @property bool           $is_from_care_center
     * @property bool is_manual
     * @property \CircleLinkHealth\Customer\Entities\User|null                                  $schedulerUser
     * @property \CircleLinkHealth\Customer\Entities\User                                       $inboundUser
     * @property \App\Note|null                                                                 $note
     * @property \CircleLinkHealth\Customer\Entities\User|null                                  $outboundUser
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereAttemptNote($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereCallTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereCalledDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereInboundCpmId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereInboundPhoneNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereIsCpmOutbound($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereNoteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereOutboundCpmId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereOutboundPhoneNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereScheduledDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereScheduler($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereService($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereWindowEnd($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereWindowStart($value)
     * @mixin \Eloquent
     *
     * @property int|null $is_manual
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call ofMonth(\Carbon\Carbon $monthYear)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call ofStatus($status)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call scheduled()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereIsManual($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereSubType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Call whereType($value)
     */
    class Call extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesQuestionSets.
     *
     * @property int                   $qsid
     * @property int                   $provider_id
     * @property string|null           $qs_type
     * @property int                   $qs_sort
     * @property int|null              $qid
     * @property int|null              $answer_response
     * @property int|null              $aid
     * @property int|null              $low
     * @property int|null              $high
     * @property string|null           $action
     * @property \App\CPRulesAnswers   $answer
     * @property \App\CPRulesQuestions $question
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereAction($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereAid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereAnswerResponse($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereHigh($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereLow($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQsSort($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQsType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets whereQsid($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesQuestionSets query()
     */
    class CPRulesQuestionSets extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesAnswers.
     *
     * @property int         $aid
     * @property string      $value
     * @property string|null $alt_answers
     * @property int|null    $a_sort
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereASort($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereAid($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereAltAnswers($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers whereValue($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesAnswers query()
     */
    class CPRulesAnswers extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CarePlan.
     *
     * @property int                                                        $id
     * @property string                                                     $mode
     * @property int                                                        $user_id
     * @property int|null                                                   $provider_approver_id
     * @property int|null                                                   $qa_approver_id
     * @property int                                                        $care_plan_template_id
     * @property string                                                     $type
     * @property string                                                     $status
     * @property \Carbon\Carbon                                             $qa_date
     * @property \Carbon\Carbon                                             $provider_date
     * @property string|null                                                $last_printed
     * @property \Carbon\Carbon                                             $created_at
     * @property \Carbon\Carbon                                             $updated_at
     * @property \App\CarePlanTemplate                                      $carePlanTemplate
     * @property \App\CareplanAssessment                                    $assessment
     * @property \CircleLinkHealth\Customer\Entities\User                   $patient
     * @property \App\Models\Pdf[]|\Illuminate\Database\Eloquent\Collection $pdfs
     * @property \CircleLinkHealth\Customer\Entities\User|null              $providerApproverUser
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCarePlanTemplateId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereLastPrinted($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereMode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereProviderApproverId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereProviderDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereQaApproverId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereQaDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUserId($value)
     * @mixin \Eloquent
     *
     * @property int|null                                                                             $first_printed_by
     * @property \Illuminate\Support\Carbon|null                                                      $first_printed
     * @property string                                                                               $provider_approver_name
     * @property \App\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]       $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereFirstPrinted($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereFirstPrintedBy($value)
     */
    class CarePlan extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\ChargeableService.
     *
     * @property int                                                                                                  $id
     * @property string                                                                                               $code
     * @property string|null                                                                                          $description
     * @property float|null                                                                                           $amount
     * @property \Illuminate\Support\Carbon|null                                                                      $created_at
     * @property \Illuminate\Support\Carbon|null                                                                      $updated_at
     * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $patientSummaries
     * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection              $practices
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                  $providers
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                       $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereAmount($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ChargeableService whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class ChargeableService extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Media.
     *
     * @property int                                                   $id
     * @property int                                                   $model_id
     * @property string                                                $model_type
     * @property string                                                $collection_name
     * @property string                                                $name
     * @property string                                                $file_name
     * @property string|null                                           $mime_type
     * @property string                                                $disk
     * @property int                                                   $size
     * @property array                                                 $manipulations
     * @property array                                                 $custom_properties
     * @property array                                                 $responsive_images
     * @property int|null                                              $order_column
     * @property \Illuminate\Support\Carbon|null                       $created_at
     * @property \Illuminate\Support\Carbon|null                       $updated_at
     * @property mixed                                                 $extension
     * @property mixed                                                 $human_readable_size
     * @property mixed                                                 $type
     * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection $model
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\MediaLibrary\Models\Media ordered()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereCollectionName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereCustomProperties($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereDisk($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereFileName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereManipulations($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereMimeType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereModelId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereModelType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereOrderColumn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereResponsiveImages($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereSize($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Media whereUpdatedAt($value)
     * @mixin \Eloquent
     */
    class Media extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\EmrDirectAddress.
     *
     * @property int                                           $id
     * @property string                                        $emrDirectable_type
     * @property int                                           $emrDirectable_id
     * @property string                                        $address
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $emrDirectable
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereAddress($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereEmrDirectableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereEmrDirectableType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress query()
     */
    class EmrDirectAddress extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\ProblemCodeSystem.
     *
     * @property int                 $id
     * @property string              $name
     * @property \Carbon\Carbon|null $created_at
     * @property \Carbon\Carbon|null $updated_at
     * @property string|null         $deleted_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProblemCodeSystem query()
     */
    class ProblemCodeSystem extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\SnomedToICD9Map.
     *
     * @property int      $id
     * @property int      $ccm_eligible
     * @property string   $code
     * @property string   $name
     * @property float    $avg_usage
     * @property int      $is_nec
     * @property int      $snomed_code
     * @property string   $snomed_name
     * @property int|null $cpm_problem_id
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereAvgUsage($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereCcmEligible($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereCpmProblemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereIsNec($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereSnomedCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map whereSnomedName($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\SnomedToICD9Map query()
     */
    class SnomedToICD9Map extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Message.
     *
     * @property int                                      $id
     * @property string|null                              $sender_email
     * @property string|null                              $receiver_email
     * @property string                                   $body
     * @property string                                   $subject
     * @property string                                   $type
     * @property int                                      $sender_cpm_id
     * @property int                                      $receiver_cpm_id
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property int|null                                 $note_id
     * @property string|null                              $seen_on
     * @property \CircleLinkHealth\Customer\Entities\User $recipient
     * @property \CircleLinkHealth\Customer\Entities\User $sender
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereBody($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereNoteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereReceiverCpmId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereReceiverEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSeenOn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSenderCpmId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSenderEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereSubject($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Message query()
     */
    class Message extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesUCP.
     *
     * @property int                                      $ucp_id
     * @property int|null                                 $items_id
     * @property int|null                                 $user_id
     * @property string|null                              $meta_key
     * @property string|null                              $meta_value
     * @property \App\CPRulesItem|null                    $item
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereItemsId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereMetaKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereMetaValue($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereUcpId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP query()
     */
    class CPRulesUCP extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesItem.
     *
     * @property int                                                             $items_id
     * @property int|null                                                        $pcp_id
     * @property int|null                                                        $items_parent
     * @property int|null                                                        $qid
     * @property string                                                          $care_item_id
     * @property string                                                          $name
     * @property string                                                          $display_name
     * @property string                                                          $description
     * @property string|null                                                     $items_text
     * @property string|null                                                     $deleted_at
     * @property \App\CPRulesItemMeta[]|\Illuminate\Database\Eloquent\Collection $meta
     * @property \App\CPRulesPCP|null                                            $pcp
     * @property \App\CPRulesQuestions|null                                      $question
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereCareItemId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsParent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsText($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem wherePcpId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereQid($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem query()
     */
    class CPRulesItem extends \Eloquent
    {
    }
}

namespace App\Entities{
    /**
     * App\Entities\CcdaRequest.
     *
     * @property int                                  $id
     * @property int|null                             $ccda_id
     * @property string                               $vendor
     * @property int                                  $patient_id
     * @property int                                  $department_id
     * @property int                                  $practice_id
     * @property int|null                             $successful_call
     * @property int|null                             $document_id
     * @property \Carbon\Carbon|null                  $created_at
     * @property \Carbon\Carbon|null                  $updated_at
     * @property \App\Models\MedicalRecords\Ccda|null $ccda
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereCcdaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereDepartmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereDocumentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereSuccessfulCall($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest whereVendor($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\CcdaRequest query()
     */
    class CcdaRequest extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\Enrollee.
     *
     * @property int                                               $id
     * @property string|null                                       $medical_record_type
     * @property int|null                                          $medical_record_id
     * @property int|null                                          $user_id
     * @property int|null                                          $provider_id
     * @property int|null                                          $practice_id
     * @property int|null                                          $care_ambassador_id
     * @property int                                               $total_time_spent
     * @property string|null                                       $last_call_outcome
     * @property string|null                                       $last_call_outcome_reason
     * @property string                                            $mrn
     * @property string                                            $first_name
     * @property string                                            $last_name
     * @property string                                            $address
     * @property string                                            $address_2
     * @property string                                            $city
     * @property string                                            $state
     * @property string                                            $zip
     * @property mixed                                             $primary_phone
     * @property string                                            $other_phone
     * @property string                                            $home_phone
     * @property string                                            $cell_phone
     * @property \Carbon\Carbon|null                               $dob
     * @property string                                            $lang
     * @property string                                            $invite_code
     * @property string                                            $status
     * @property int                                               $attempt_count
     * @property string|null                                       $preferred_days
     * @property string|null                                       $preferred_window
     * @property string|null                                       $invite_sent_at
     * @property string|null                                       $consented_at
     * @property string|null                                       $last_attempt_at
     * @property string|null                                       $invite_opened_at
     * @property \Carbon\Carbon|null                               $created_at
     * @property \Carbon\Carbon|null                               $updated_at
     * @property \Carbon\Carbon|null                               $soft_rejected_callback
     * @property string                                            $primary_insurance
     * @property string                                            $secondary_insurance
     * @property string                                            $tertiary_insurance
     * @property int|null                                          $has_copay
     * @property string                                            $email
     * @property string                                            $last_encounter
     * @property string                                            $referring_provider_name
     * @property int|null                                          $confident_provider_guess
     * @property string                                            $problems
     * @property int                                               $cpm_problem_1
     * @property int                                               $cpm_problem_2
     * @property string|null                                       $color
     * @property \App\CareAmbassador|null                          $careAmbassador
     * @property mixed                                             $practice_name
     * @property mixed                                             $provider_full_name
     * @property \CircleLinkHealth\Customer\Entities\Practice|null $practice
     * @property \CircleLinkHealth\Customer\Entities\User|null     $provider
     * @property mixed                                             $primary_phone_number
     * @property \CircleLinkHealth\Customer\Entities\User|null     $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee toCall()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee toSMS()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAddress($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAddress2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereAttemptCount($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCareAmbassadorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCellPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereColor($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereConfidentProviderGuess($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereConsentedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCpmProblem1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCpmProblem2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereDob($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereHasCopay($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereHomePhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteOpenedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereInviteSentAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLang($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastAttemptAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastCallOutcome($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastCallOutcomeReason($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastEncounter($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMedicalRecordType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereMrn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereOtherPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePreferredDays($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePreferredWindow($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePrimaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee wherePrimaryPhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereProblems($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereReferringProviderName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereSecondaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereState($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereTertiaryInsurance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereTotalTimeSpent($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereZip($value)
     * @mixin \Eloquent
     *
     * @property int|null                 $batch_id
     * @property int|null                 $eligibility_job_id
     * @property int|null                 $care_ambassador_user_id
     * @property \App\EligibilityJob|null $eligibilityJob
     * @property $cell_phone_e164
     * @property $home_phone_e164
     * @property $other_phone_e164
     * @property mixed                                                                          $primary_phone_e164
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     * @property \App\TargetPatient                                                             $targetPatient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee query()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereBatchId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereCareAmbassadorUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereEligibilityJobId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Enrollee whereSoftRejectedCallback($value)
     */
    class Enrollee extends \Eloquent
    {
    }
}

namespace App{
    /**
     * App\CPRulesItemMeta.
     *
     * @property int                   $itemmeta_id
     * @property int|null              $items_id
     * @property string|null           $meta_key
     * @property string|null           $meta_value
     * @property \App\CPRulesItem|null $CPRulesItem
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereItemmetaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereItemsId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereMetaKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereMetaValue($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta query()
     */
    class CPRulesItemMeta extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\TwoFA\Entities{
    /**
     * CircleLinkHealth\TwoFA\Entities\AuthyUser.
     *
     * @property int                                                                            $id
     * @property int|null                                                                       $user_id
     * @property int                                                                            $is_authy_enabled
     * @property string|null                                                                    $authy_id
     * @property string|null                                                                    $authy_method
     * @property string|null                                                                    $country_code
     * @property string|null                                                                    $phone_number
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\User|null                                  $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereAuthyId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereAuthyMethod($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereCountryCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereIsAuthyEnabled($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser wherePhoneNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereUserId($value)
     * @mixin \Eloquent
     */
    class AuthyUser extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Core\Entities{
    /**
     * CircleLinkHealth\Core\Entities\BaseModel.
     *
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Core\Entities\BaseModel query()
     */
    class BaseModel extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\TimeTracking\Entities{
    /**
     * CircleLinkHealth\TimeTracking\Entities\Activity.
     *
     * @property int                                                                                             $id
     * @property string|null                                                                                     $type
     * @property int                                                                                             $duration
     * @property string|null                                                                                     $duration_unit
     * @property int                                                                                             $patient_id
     * @property int                                                                                             $provider_id
     * @property int                                                                                             $logger_id
     * @property int                                                                                             $comment_id
     * @property bool                                                                                            $is_behavioral
     * @property int|null                                                                                        $sequence_id
     * @property string                                                                                          $obs_message_id
     * @property string                                                                                          $logged_from
     * @property string                                                                                          $performed_at
     * @property string                                                                                          $performed_at_gmt
     * @property \Carbon\Carbon                                                                                  $created_at
     * @property \Carbon\Carbon                                                                                  $updated_at
     * @property \Carbon\Carbon|null                                                                             $deleted_at
     * @property int|null                                                                                        $page_timer_id
     * @property \CircleLinkHealth\Customer\Entities\NurseCareRateLog[]|\Illuminate\Database\Eloquent\Collection $careRateLogs
     * @property \App\CcmTimeApiLog                                                                              $ccmApiTimeSentLog
     * @property mixed                                                                                           $performed_at_year_month
     * @property \CircleLinkHealth\TimeTracking\Entities\ActivityMeta[]|\Illuminate\Database\Eloquent\Collection $meta
     * @property \CircleLinkHealth\TimeTracking\Entities\PageTimer                                               $pageTime
     * @property \CircleLinkHealth\Customer\Entities\User                                                        $patient
     * @property \CircleLinkHealth\Customer\Entities\User                                                        $provider
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                  $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdBy(\CircleLinkHealth\Customer\Entities\User $user)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdThisMonth($field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdOn(Carbon $date, $field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdToday($field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity createdYesterday($field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCommentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDurationUnit($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereLoggedFrom($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereLoggerId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereObsMessageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePageTimerId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePerformedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity wherePerformedAtGmt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereSequenceId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\Activity whereIsBehavioral($value)
     */
    class Activity extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\TimeTracking\Entities{
    /**
     * CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest.
     *
     * @property int                                                   $id
     * @property int|null                                              $is_approved
     * @property int                                                   $is_behavioral
     * @property string|null                                           $type
     * @property int                                                   $duration_seconds
     * @property int                                                   $patient_id
     * @property int                                                   $requester_id
     * @property int|null                                              $activity_id
     * @property \Illuminate\Support\Carbon|null                       $performed_at
     * @property string|null                                           $comment
     * @property \Illuminate\Support\Carbon|null                       $created_at
     * @property \Illuminate\Support\Carbon|null                       $updated_at
     * @property \Illuminate\Support\Carbon|null                       $deleted_at
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity|null $activity
     * @property \CircleLinkHealth\Customer\Entities\User              $patient
     * @property \CircleLinkHealth\Customer\Entities\User              $requester
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest newQuery()
     * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest query()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereActivityId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereComment($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereDurationSeconds($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereIsApproved($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereIsBehavioral($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest wherePerformedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereRequesterId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest withoutTrashed()
     * @mixin \Eloquent
     */
    class OfflineActivityTimeRequest extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\TimeTracking\Entities{
    /**
     * CircleLinkHealth\TimeTracking\Entities\ActivityMeta.
     *
     * @property int                                              $id
     * @property int                                              $activity_id
     * @property int                                              $comment_id
     * @property string                                           $message_id
     * @property string|null                                      $meta_key
     * @property string                                           $meta_value
     * @property \Carbon\Carbon                                   $created_at
     * @property \Carbon\Carbon                                   $updated_at
     * @property \Carbon\Carbon|null                              $deleted_at
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity $activity
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\ActivityMeta onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereActivityId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereCommentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMessageId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMetaKey($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereMetaValue($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ActivityMeta whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\ActivityMeta withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\ActivityMeta withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\ActivityMeta newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\ActivityMeta newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\ActivityMeta query()
     */
    class ActivityMeta extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\TimeTracking\Entities{
    /**
     * CircleLinkHealth\TimeTracking\Entities\PageTimer.
     *
     * @property int                                                                                         $id
     * @property int                                                                                         $billable_duration
     * @property int                                                                                         $duration
     * @property string|null                                                                                 $duration_unit
     * @property int                                                                                         $patient_id
     * @property int                                                                                         $provider_id
     * @property string                                                                                      $start_time
     * @property string                                                                                      $end_time
     * @property string|null                                                                                 $redirect_to
     * @property string|null                                                                                 $url_full
     * @property string|null                                                                                 $url_short
     * @property string                                                                                      $activity_type
     * @property string                                                                                      $title
     * @property string                                                                                      $query_string
     * @property int                                                                                         $program_id
     * @property string|null                                                                                 $ip_addr
     * @property \Carbon\Carbon                                                                              $created_at
     * @property \Carbon\Carbon                                                                              $updated_at
     * @property string|null                                                                                 $processed
     * @property string|null                                                                                 $rule_params
     * @property int|null                                                                                    $rule_id
     * @property \Carbon\Carbon|null                                                                         $deleted_at
     * @property string|null                                                                                 $user_agent
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $activities
     * @property \CircleLinkHealth\Customer\Entities\User                                                    $logger
     * @property \CircleLinkHealth\Customer\Entities\User                                                    $patient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdThisMonth($field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdOn(Carbon $date, $field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdToday($field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer createdYesterday($field = 'created_at')
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\PageTimer onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereActivityType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereActualEndTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereActualStartTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereBillableDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereDuration($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereDurationUnit($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereEndTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereIpAddr($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereProcessed($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereQueryString($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereRedirectTo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereRuleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereRuleParams($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereStartTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereTitle($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUrlFull($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUrlShort($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PageTimer whereUserAgent($value)
     * @method static \Illuminate\Database\Query\Builder|\App\PageTimer withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\PageTimer withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity                               $activity
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TimeTracking\Entities\PageTimer query()
     */
    class PageTimer extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\UserPasswordsHistory.
     *
     * @property int            $id
     * @property int            $user_id
     * @property string         $older_password
     * @property string         $old_password
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereOldPassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereOlderPassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereUserId($value)
     * @mixin \Eloquent
     */
    class UserPasswordsHistory extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * Class PracticeRoleUser.
     *
     * @property int                                                                            $program_id
     * @property int                                                                            $user_id
     * @property int                                                                            $role_id
     * @property int|null                                                                       $has_admin_rights
     * @property int|null                                                                       $send_billing_reports
     * @property \Illuminate\Support\Carbon|null                                                $created_at
     * @property \Illuminate\Support\Carbon|null                                                $updated_at
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereHasAdminRights($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereRoleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereSendBillingReports($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PracticeRoleUser whereUserId($value)
     * @mixin \Eloquent
     */
    class PracticeRoleUser extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Practice.
     *
     * @property int                                                                                     $id
     * @property int|null                                                                                $ehr_id
     * @property int|null                                                                                $user_id
     * @property string                                                                                  $name
     * @property string|null                                                                             $display_name
     * @property int                                                                                     $active
     * @property float                                                                                   $clh_pppm
     * @property int                                                                                     $term_days
     * @property string|null                                                                             $federal_tax_id
     * @property int|null                                                                                $same_ehr_login
     * @property int|null                                                                                $same_clinical_contact
     * @property int                                                                                     $auto_approve_careplans
     * @property int                                                                                     $send_alerts
     * @property string|null                                                                             $weekly_report_recipients
     * @property string                                                                                  $invoice_recipients
     * @property string                                                                                  $bill_to_name
     * @property string|null                                                                             $external_id
     * @property string                                                                                  $outgoing_phone_number
     * @property \Carbon\Carbon                                                                          $created_at
     * @property \Carbon\Carbon                                                                          $updated_at
     * @property string|null                                                                             $deleted_at
     * @property string|null                                                                             $sms_marketing_number
     * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                        $careplan
     * @property \CircleLinkHealth\Customer\Entities\Ehr|null                                            $ehr
     * @property mixed                                                                                   $formatted_name
     * @property mixed                                                                                   $primary_location_id
     * @property mixed                                                                                   $subdomain
     * @property \CircleLinkHealth\Customer\Entities\User|null                                           $lead
     * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection $locations
     * @property \App\CPRulesPCP[]|\Illuminate\Database\Eloquent\Collection                              $pcp
     * @property \CircleLinkHealth\Customer\Entities\Settings[]|\Illuminate\Database\Eloquent\Collection $settings
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection     $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice active()
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Practice onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereActive($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereAutoApproveCareplans($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereBillToName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereClhPppm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereEhrId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereExternalId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereFederalTaxId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereInvoiceRecipients($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereOutgoingPhoneNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSameClinicalContact($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSameEhrLogin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSendAlerts($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSmsMarketingNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereTermDays($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereWeeklyReportRecipients($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Practice withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Practice withoutTrashed()
     * @mixin \Eloquent
     *
     * @property int|null                                                                                                  $saas_account_id
     * @property \App\CareAmbassadorLog[]|\Illuminate\Database\Eloquent\Collection                                         $careAmbassadorLogs
     * @property \App\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                                         $chargeableServices
     * @property \App\EnrolleeCustomFilter[]|\Illuminate\Database\Eloquent\Collection                                      $enrolleeCustomFilters
     * @property \App\PracticeEnrollmentTips                                                                               $enrollmentTips
     * @property string                                                                                                    $number_with_dashes
     * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                                                     $media
     * @property \Illuminate\Notifications\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                            $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null                                                      $saasAccount
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice activeBillable()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice authUserCanAccess($softwareOnly = false)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice authUserCannotAccess()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice enrolledPatients()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice hasServiceCode($code)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice whereSaasAccountId($value)
     */
    class Practice extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\CarePerson.
     *
     * @property int                                      $id
     * @property int                                      $alert
     * @property int                                      $user_id
     * @property int                                      $member_user_id
     * @property string                                   $type
     * @property \Carbon\Carbon                           $created_at
     * @property \Carbon\Carbon                           $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereAlert($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereMemberUserId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CarePerson newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CarePerson newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\CarePerson query()
     */
    class CarePerson extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Ehr.
     *
     * @property int                                                                                     $id
     * @property string                                                                                  $name
     * @property string                                                                                  $pdf_report_handler
     * @property \Carbon\Carbon|null                                                                     $created_at
     * @property \Carbon\Carbon|null                                                                     $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr wherePdfReportHandler($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Ehr whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     * @property \App\TargetPatient[]|\Illuminate\Database\Eloquent\Collection                  $targetPatient
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Ehr newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Ehr newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Ehr query()
     */
    class Ehr extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Settings.
     *
     * @property int                                           $id
     * @property int                                           $settingsable_id
     * @property string                                        $settingsable_type
     * @property string                                        $careplan_mode
     * @property int                                           $auto_approve_careplans
     * @property int                                           $dm_pdf_careplan
     * @property int                                           $dm_pdf_notes
     * @property int                                           $dm_audit_reports
     * @property int                                           $email_careplan_approval_reminders
     * @property int                                           $email_note_was_forwarded
     * @property int                                           $email_weekly_report
     * @property int                                           $efax_pdf_careplan
     * @property int                                           $efax_pdf_notes
     * @property int                                           $efax_audit_reports
     * @property string                                        $default_target_bp
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $settingsable
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereAutoApproveCareplans($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereCareplanMode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDefaultTargetBp($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDmAuditReports($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDmPdfCareplan($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereDmPdfNotes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEfaxAuditReports($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEfaxPdfCareplan($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEfaxPdfNotes($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEmailCareplanApprovalReminders($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEmailNoteWasForwarded($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereEmailWeeklyReport($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereRnCanApproveCareplans($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereSettingsableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereSettingsableType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Settings whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property int                                                                            $dm_careplan_approval_reminders
     * @property float|null                                                                     $note_font_size
     * @property string                                                                         $bill_to
     * @property int                                                                            $api_auto_pull
     * @property int|null                                                                       $default_chargeable_service_id
     * @property int                                                                            $twilio_enabled
     * @property int                                                                            $twilio_recordings_enabled
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereApiAutoPull($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereBillTo($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereDefaultChargeableServiceId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereDmCareplanApprovalReminders($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereNoteFontSize($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereTwilioEnabled($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Settings whereTwilioRecordingsEnabled($value)
     */
    class Settings extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Appointment.
     *
     * @property int                                           $id
     * @property int                                           $patient_id
     * @property int                                           $author_id
     * @property int|null                                      $provider_id
     * @property string                                        $date
     * @property string                                        $time
     * @property string                                        $status
     * @property string                                        $comment
     * @property int                                           $was_completed
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property string                                        $type
     * @property \CircleLinkHealth\Customer\Entities\User      $author
     * @property \CircleLinkHealth\Customer\Entities\User      $patient
     * @property \CircleLinkHealth\Customer\Entities\User|null $provider
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereAuthorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereComment($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereProviderId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereWasCompleted($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Appointment newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Appointment newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Appointment query()
     */
    class Appointment extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\SaasAccount.
     *
     * @property int                                                                                     $id
     * @property string                                                                                  $name
     * @property string                                                                                  $slug
     * @property string|null                                                                             $logo_path
     * @property \Illuminate\Support\Carbon|null                                                         $created_at
     * @property \Illuminate\Support\Carbon|null                                                         $updated_at
     * @property string|null                                                                             $deleted_at
     * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                                   $media
     * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $practices
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]          $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection     $users
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount newQuery()
     * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount onlyTrashed()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount query()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereLogoPath($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereSlug($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount withoutTrashed()
     * @mixin \Eloquent
     */
    class SaasAccount extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\NurseContactWindow.
     *
     * @property int                                       $id
     * @property int                                       $nurse_info_id
     * @property \Carbon\Carbon                            $date
     * @property int                                       $day_of_week
     * @property string                                    $window_time_start
     * @property string                                    $window_time_end
     * @property \Carbon\Carbon|null                       $created_at
     * @property \Carbon\Carbon|null                       $updated_at
     * @property \Carbon\Carbon|null                       $deleted_at
     * @property mixed                                     $day_name
     * @property \CircleLinkHealth\Customer\Entities\Nurse $nurse
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\NurseContactWindow onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow upcoming()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereDayOfWeek($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereNurseInfoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereWindowTimeEnd($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseContactWindow whereWindowTimeStart($value)
     * @method static \Illuminate\Database\Query\Builder|\App\NurseContactWindow withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\NurseContactWindow withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseContactWindow query()
     */
    class NurseContactWindow extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Family.
     *
     * @property int                                                                                    $id
     * @property string|null                                                                            $name
     * @property int|null                                                                               $created_by
     * @property \Carbon\Carbon|null                                                                    $created_at
     * @property \Carbon\Carbon|null                                                                    $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Patient[]|\Illuminate\Database\Eloquent\Collection $patients
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereCreatedBy($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Family whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Family newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Family newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Family query()
     */
    class Family extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\User.
     *
     * @property int                                                                                         $id
     * @property int                                                                                         $count_ccm_time
     * @property string                                                                                      $username
     * @property string                                                                                      $program_id
     * @property string                                                                                      $password
     * @property string                                                                                      $email
     * @property \Carbon\Carbon                                                                              $user_registered
     * @property int                                                                                         $user_status
     * @property int                                                                                         $auto_attach_programs
     * @property string                                                                                      $display_name
     * @property string                                                                                      $first_name
     * @property string                                                                                      $last_name
     * @property string|null                                                                                 $suffix
     * @property string                                                                                      $address
     * @property string                                                                                      $address2
     * @property string                                                                                      $city
     * @property string                                                                                      $state
     * @property string                                                                                      $zip
     * @property string|null                                                                                 $timezone
     * @property string                                                                                      $status
     * @property int                                                                                         $access_disabled
     * @property int|null                                                                                    $is_auto_generated
     * @property string|null                                                                                 $remember_token
     * @property \Carbon\Carbon                                                                              $created_at
     * @property \Carbon\Carbon                                                                              $updated_at
     * @property string|null                                                                                 $deleted_at
     * @property string|null                                                                                 $last_login
     * @property int                                                                                         $is_online
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $activities
     * @property \CircleLinkHealth\Customer\Entities\Appointment[]|\Illuminate\Database\Eloquent\Collection  $appointments
     * @property \App\CareAmbassador                                                                         $careAmbassador
     * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection                                    $careItems
     * @property \App\CarePlan                                                                               $carePlan
     * @property \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection   $careTeamMembers
     * @property \App\Models\CCD\Allergy[]|\Illuminate\Database\Eloquent\Collection                          $ccdAllergies
     * @property \App\Models\CCD\CcdInsurancePolicy[]|\Illuminate\Database\Eloquent\Collection               $ccdInsurancePolicies
     * @property \App\Models\CCD\Medication[]|\Illuminate\Database\Eloquent\Collection                       $ccdMedications
     * @property \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection                          $ccdProblems
     * @property \App\Models\MedicalRecords\Ccda[]|\Illuminate\Database\Eloquent\Collection                  $ccdas
     * @property \App\Comment[]|\Illuminate\Database\Eloquent\Collection                                     $comment
     * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection                     $cpmBiometrics
     * @property \App\Models\CPM\Biometrics\CpmBloodPressure                                                 $cpmBloodPressure
     * @property \App\Models\CPM\Biometrics\CpmBloodSugar                                                    $cpmBloodSugar
     * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection                     $cpmLifestyles
     * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection               $cpmMedicationGroups
     * @property \App\Models\CPM\CpmMisc[]|\Illuminate\Database\Eloquent\Collection                          $cpmMiscs
     * @property \App\Models\CPM\CpmProblem[]|\Illuminate\Database\Eloquent\Collection                       $cpmProblems
     * @property \App\Models\CPM\Biometrics\CpmSmoking                                                       $cpmSmoking
     * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection                       $cpmSymptoms
     * @property \App\Models\CPM\Biometrics\CpmWeight                                                        $cpmWeight
     * @property \App\Models\EmailSettings                                                                   $emailSettings
     * @property \App\EmrDirectAddress[]|\Illuminate\Database\Eloquent\Collection                            $emrDirect
     * @property \App\ForeignId[]|\Illuminate\Database\Eloquent\Collection                                   $foreignId
     * @property \App\User[]|\Illuminate\Database\Eloquent\Collection                                        $forwardAlertsTo
     * @property mixed                                                                                       $active_date
     * @property mixed                                                                                       $age
     * @property mixed                                                                                       $agent_email
     * @property mixed                                                                                       $agent_name
     * @property mixed                                                                                       $agent_phone
     * @property mixed                                                                                       $agent_relationship
     * @property mixed                                                                                       $agent_telephone
     * @property mixed                                                                                       $billing_provider_i_d
     * @property string                                                                                      $billing_provider_name
     * @property mixed                                                                                       $birth_date
     * @property mixed                                                                                       $care_plan_provider_approver
     * @property mixed                                                                                       $care_plan_provider_approver_date
     * @property mixed                                                                                       $care_plan_q_a_approver
     * @property mixed                                                                                       $care_plan_q_a_date
     * @property mixed                                                                                       $care_plan_status
     * @property mixed                                                                                       $care_team
     * @property \Collection                                                                                 $care_team_receives_alerts
     * @property mixed                                                                                       $careplan_last_printed
     * @property mixed                                                                                       $careplan_mode
     * @property mixed                                                                                       $ccm_status
     * @property mixed                                                                                       $ccm_time
     * @property mixed                                                                                       $bhi_time
     * @property mixed                                                                                       $consent_date
     * @property mixed                                                                                       $daily_reminder_areas
     * @property mixed                                                                                       $daily_reminder_optin
     * @property mixed                                                                                       $daily_reminder_time
     * @property mixed                                                                                       $date_paused
     * @property mixed                                                                                       $date_withdrawn
     * @property mixed                                                                                       $emr_direct_address
     * @property mixed                                                                                       $full_name
     * @property mixed                                                                                       $full_name_with_id
     * @property mixed                                                                                       $gender
     * @property mixed                                                                                       $home_phone_number
     * @property mixed                                                                                       $hospital_reminder_areas
     * @property mixed                                                                                       $hospital_reminder_optin
     * @property mixed                                                                                       $hospital_reminder_time
     * @property mixed                                                                                       $lead_contact_i_d
     * @property mixed                                                                                       $m_r_n
     * @property mixed                                                                                       $mobile_phone_number
     * @property mixed                                                                                       $mrn_number
     * @property mixed                                                                                       $npi_number
     * @property mixed                                                                                       $phone
     * @property mixed                                                                                       $preferred_cc_contact_days
     * @property mixed                                                                                       $preferred_contact_language
     * @property mixed                                                                                       $preferred_contact_location
     * @property mixed                                                                                       $preferred_contact_method
     * @property mixed                                                                                       $preferred_contact_time
     * @property mixed                                                                                       $prefix
     * @property mixed                                                                                       $primary_phone
     * @property mixed                                                                                       $primary_practice_id
     * @property string                                                                                      $primary_practice_name
     * @property mixed                                                                                       $registration_date
     * @property mixed                                                                                       $send_alert_to
     * @property mixed                                                                                       $specialty
     * @property mixed                                                                                       $work_phone_number
     * @property UserPasswordsHistory|null                                                                   $passwordsHistory
     * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection                                        $inboundCalls
     * @property \App\Message[]|\Illuminate\Database\Eloquent\Collection                                     $inboundMessages
     * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection     $locations
     * @property \App\Note[]|\Illuminate\Database\Eloquent\Collection                                        $notes
     * @property \Illuminate\Notifications\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection
     *     $notifications
     * @property \CircleLinkHealth\Customer\Entities\Nurse                                                   $nurseInfo
     * @property \App\Observation[]|\Illuminate\Database\Eloquent\Collection                                 $observations
     * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection                                        $outboundCalls
     * @property \App\Message[]|\Illuminate\Database\Eloquent\Collection                                     $outboundMessages
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $patientActivities
     * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection
     *     $patientDemographics
     * @property \CircleLinkHealth\Customer\Entities\Patient                                                $patientInfo
     * @property \CircleLinkHealth\Customer\Entities\PhoneNumber[]|\Illuminate\Database\Eloquent\Collection $phoneNumbers
     * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection    $practices
     * @property \CircleLinkHealth\Customer\Entities\Practice                                               $primaryPractice
     * @property \CircleLinkHealth\Customer\Entities\ProviderInfo                                           $providerInfo
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]             $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection        $roles
     * @property mixed                                                                                      $email_address
     * @property \App\CPRulesUCP[]|\Illuminate\Database\Eloquent\Collection                                 $ucp
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User exceptType($type)
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User hasBillingProvider($billing_provider_id)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User intersectLocationsWith($user)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User intersectPracticesWith($user)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User ofType($type)
     * @method static \Illuminate\Database\Query\Builder|\App\User onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessDisabled($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAutoAttachPrograms($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCountCcmTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsAutoGenerated($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsOnline($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLogin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereProgramId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereState($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSuffix($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereTimezone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserRegistered($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereZip($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\User withCareTeamOfType($type)
     * @method static \Illuminate\Database\Query\Builder|\App\User withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\User withoutTrashed()
     * @mixin \Eloquent
     *
     * @property int|null                                                                                             $saas_account_id
     * @property int                                                                                                  $skip_browser_checks               Skip compatible browser checks when the user logs in
     * @property string|null                                                                                          $last_session_id
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection          $activitiesAsProvider
     * @property \CircleLinkHealth\TwoFA\Entities\AuthyUser                                                           $authyUser
     * @property \App\CareplanAssessment                                                                              $carePlanAssessment
     * @property \App\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                                    $chargeableServices
     * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[]                                  $clients
     * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection              $clinicalEmergencyContactLocations
     * @property \App\TargetPatient                                                                                   $ehrInfo
     * @property \CircleLinkHealth\Customer\Entities\EhrReportWriterInfo                                              $ehrReportWriterInfo
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                  $forwardedAlertsBy
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                  $forwardedCarePlanApprovalEmailsBy
     * @property mixed                                                                                                $timezone_abbr
     * @property mixed                                                                                                $timezone_offset
     * @property mixed                                                                                                $timezone_offset_hours
     * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection                                                 $inboundActivities
     * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                                                $media
     * @property \CircleLinkHealth\TimeTracking\Entities\PageTimer[]|\Illuminate\Database\Eloquent\Collection         $pageTimersAsProvider
     * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $patientSummaries
     * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection            $perms
     * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null                                                 $saasAccount
     * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[]                                   $tokens
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User careCoaches()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiChargeable()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiEligible()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofPractice($practiceId)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User practicesWhereHasRoles($roleIds, $onlyActive = false)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereLastSessionId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereSaasAccountId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereSkipBrowserChecks($value)
     */
    class User extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Role.
     *
     * @property int                                                                                       $id
     * @property string                                                                                    $name
     * @property string|null                                                                               $display_name
     * @property string|null                                                                               $description
     * @property \Carbon\Carbon                                                                            $created_at
     * @property \Carbon\Carbon                                                                            $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection $perms
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection       $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role query()
     */
    class Role extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\PatientMonthlySummary.
     *
     * @property int                                         $id
     * @property int                                         $patient_id
     * @property int                                         $ccm_time
     * @property int                                         $bhi_time
     * @property \Carbon\Carbon                              $month_year
     * @property int                                         $no_of_calls
     * @property int                                         $no_of_successful_calls
     * @property string                                      $billable_problem1
     * @property string                                      $billable_problem1_code
     * @property string                                      $billable_problem2
     * @property string                                      $billable_problem2_code
     * @property int                                         $approved
     * @property int                                         $rejected
     * @property int|null                                    $actor_id
     * @property \Carbon\Carbon|null                         $created_at
     * @property \Carbon\Carbon|null                         $updated_at
     * @property int                                         $total_time
     * @property \CircleLinkHealth\Customer\Entities\User    $actor
     * @property \CircleLinkHealth\Customer\Entities\Patient $patient_info
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary getCurrent()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary getForMonth(\Carbon\Carbon $month)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereActorId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereApproved($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereBillableProblem1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereBillableProblem1Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereBillableProblem2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereBillableProblem2Code($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereCcmTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereMonthYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereNoOfCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereNoOfSuccessfulCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary wherePatientInfoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereRejected($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property string|null                                                                    $closed_ccm_status
     * @property int|null                                                                       $problem_1
     * @property int|null                                                                       $problem_2
     * @property int                                                                            $is_ccm_complex
     * @property int|null                                                                       $needs_qa
     * @property \App\Models\CCD\Problem|null                                                   $billableProblem1
     * @property \App\Models\CCD\Problem|null                                                   $billableProblem2
     * @property \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection             $billableProblems
     * @property \App\ChargeableService[]|\Illuminate\Database\Eloquent\Collection              $chargeableServices
     * @property \CircleLinkHealth\Customer\Entities\User                                       $patient
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary hasServiceCode($code)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereBhiTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereClosedCcmStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereIsCcmComplex($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereNeedsQa($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary wherePatientId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereProblem1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereProblem2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary whereTotalTime($value)
     */
    class PatientMonthlySummary extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\EhrReportWriterInfo.
     *
     * @property int                                      $id
     * @property int                                      $user_id
     * @property string|null                              $google_drive_folder_path
     * @property \Illuminate\Support\Carbon|null          $created_at
     * @property \Illuminate\Support\Carbon|null          $updated_at
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereGoogleDriveFolderPath($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereUserId($value)
     * @mixin \Eloquent
     */
    class EhrReportWriterInfo extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Invite.
     *
     * @property int                                           $id
     * @property int                                           $inviter_id
     * @property int|null                                      $role_id
     * @property string                                        $email
     * @property string|null                                   $subject
     * @property string|null                                   $message
     * @property string|null                                   $code
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property \Carbon\Carbon|null                           $deleted_at
     * @property \CircleLinkHealth\Customer\Entities\User      $inviter
     * @property \CircleLinkHealth\Customer\Entities\Role|null $role
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Entities\Invite onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereInviterId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereMessage($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereRoleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereSubject($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Invite whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Entities\Invite withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Entities\Invite withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Invite newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Invite newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Invite query()
     */
    class Invite extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Holiday.
     *
     * @property int                                       $id
     * @property int                                       $nurse_info_id
     * @property \Carbon\Carbon                            $date
     * @property \Carbon\Carbon|null                       $created_at
     * @property \Carbon\Carbon|null                       $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Nurse $nurse
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereNurseInfoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Holiday whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Holiday newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Holiday newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Holiday query()
     */
    class Holiday extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\State.
     *
     * @property int                                                                                  $id
     * @property string                                                                               $name
     * @property string                                                                               $code
     * @property \CircleLinkHealth\Customer\Entities\Nurse[]|\Illuminate\Database\Eloquent\Collection $nurses
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\State whereCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\State whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\State whereName($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\State newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\State newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\State query()
     */
    class State extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\PatientContactWindow.
     *
     * @property int                                         $id
     * @property int                                         $patient_info_id
     * @property int                                         $day_of_week
     * @property string                                      $window_time_start
     * @property string                                      $window_time_end
     * @property \Carbon\Carbon|null                         $created_at
     * @property \Carbon\Carbon|null                         $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Patient $patient_info
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereDayOfWeek($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow wherePatientInfoId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereWindowTimeEnd($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientContactWindow whereWindowTimeStart($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientContactWindow newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientContactWindow newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientContactWindow query()
     */
    class PatientContactWindow extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\NurseCareRateLog.
     *
     * @property int                                                   $id
     * @property int                                                   $nurse_id
     * @property int|null                                              $activity_id
     * @property string                                                $ccm_type
     * @property int                                                   $increment
     * @property \Carbon\Carbon|null                                   $created_at
     * @property \Carbon\Carbon|null                                   $updated_at
     * @property \CircleLinkHealth\TimeTracking\Entities\Activity|null $activity
     * @property \CircleLinkHealth\Customer\Entities\Nurse             $nurse
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereActivityId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCcmType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereIncrement($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereNurseId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog query()
     */
    class NurseCareRateLog extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Nurse.
     *
     * @property int                                                                                                $id
     * @property int                                                                                                $user_id
     * @property string                                                                                             $status
     * @property string                                                                                             $license
     * @property int                                                                                                $hourly_rate
     * @property string                                                                                             $billing_type
     * @property int                                                                                                $low_rate
     * @property int                                                                                                $high_rate
     * @property int                                                                                                $spanish
     * @property \Carbon\Carbon|null                                                                                $created_at
     * @property \Carbon\Carbon|null                                                                                $updated_at
     * @property int                                                                                                $isNLC
     * @property \CircleLinkHealth\Customer\Entities\NurseCareRateLog[]|\Illuminate\Database\Eloquent\Collection    $careRateLogs
     * @property mixed                                                                                              $holidays_this_week
     * @property mixed                                                                                              $upcoming_holiday_dates
     * @property \CircleLinkHealth\Customer\Entities\Holiday[]|\Illuminate\Database\Eloquent\Collection             $holidays
     * @property \CircleLinkHealth\Customer\Entities\State[]|\Illuminate\Database\Eloquent\Collection               $states
     * @property \CircleLinkHealth\Customer\Entities\NurseMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $summary
     * @property \CircleLinkHealth\Customer\Entities\Holiday[]|\Illuminate\Database\Eloquent\Collection             $upcomingHolidays
     * @property \CircleLinkHealth\Customer\Entities\User                                                           $user
     * @property \CircleLinkHealth\Customer\Entities\NurseContactWindow[]|\Illuminate\Database\Eloquent\Collection  $windows
     * @property \CircleLinkHealth\Customer\Entities\WorkHours[]|\Illuminate\Database\Eloquent\Collection           $workhourables
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereBillingType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereHighRate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereHourlyRate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereIsNLC($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereLicense($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereLowRate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereSpanish($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Nurse whereUserId($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Nurse query()
     */
    class Nurse extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Location.
     *
     * @property int                                                                                 $id
     * @property int                                                                                 $practice_id
     * @property int                                                                                 $is_primary
     * @property string|null                                                                         $external_department_id
     * @property string                                                                              $name
     * @property string                                                                              $phone
     * @property string|null                                                                         $clinical_escalation_phone
     * @property string|null                                                                         $fax
     * @property string                                                                              $address_line_1
     * @property string|null                                                                         $address_line_2
     * @property string                                                                              $city
     * @property string                                                                              $state
     * @property string|null                                                                         $timezone
     * @property string                                                                              $postal_code
     * @property string|null                                                                         $ehr_login
     * @property string|null                                                                         $ehr_password
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property string|null                                                                         $deleted_at
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $clinicalEmergencyContact
     * @property \App\EmrDirectAddress[]|\Illuminate\Database\Eloquent\Collection                    $emrDirect
     * @property mixed                                                                               $emr_direct_address
     * @property \App\Location                                                                       $parent
     * @property \CircleLinkHealth\Customer\Entities\Practice                                        $practice
     * @property \CircleLinkHealth\Customer\Entities\Practice                                        $program
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $providers
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $user
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Location onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereAddressLine1($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereAddressLine2($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereCity($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereEhrLogin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereEhrPassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereExternalDepartmentId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereFax($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereIsPrimary($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePhone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePostalCode($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePracticeId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereState($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereTimezone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereUpdatedAt($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Location withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Location withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \App\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]       $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location whereClinicalEscalationPhone($value)
     */
    class Location extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\ProviderInfo.
     *
     * @property int                                      $id
     * @property int|null                                 $is_clinical
     * @property int                                      $user_id
     * @property string|null                              $prefix
     * @property string|null                              $npi_number
     * @property string|null                              $specialty
     * @property string                                   $created_at
     * @property string                                   $updated_at
     * @property string|null                              $deleted_at
     * @property mixed                                    $address
     * @property mixed                                    $city
     * @property mixed                                    $first_name
     * @property mixed                                    $last_name
     * @property mixed                                    $state
     * @property mixed                                    $zip
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\ProviderInfo onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereIsClinical($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereNpiNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo wherePrefix($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereSpecialty($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderInfo whereUserId($value)
     * @method static \Illuminate\Database\Query\Builder|\App\ProviderInfo withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\ProviderInfo withoutTrashed()
     * @mixin \Eloquent
     *
     * @property int                                                                            $approve_own_care_plans
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\ProviderInfo whereApproveOwnCarePlans($value)
     */
    class ProviderInfo extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\WorkHours.
     *
     * @property int                                           $id
     * @property string                                        $workhourable_type
     * @property int                                           $workhourable_id
     * @property int                                           $monday
     * @property int                                           $tuesday
     * @property int                                           $wednesday
     * @property int                                           $thursday
     * @property int                                           $friday
     * @property int                                           $saturday
     * @property int                                           $sunday
     * @property \Carbon\Carbon|null                           $created_at
     * @property \Carbon\Carbon|null                           $updated_at
     * @property string|null                                   $deleted_at
     * @property \Eloquent|\Illuminate\Database\Eloquent\Model $workhourable
     *
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\WorkHours onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereFriday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereMonday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereSaturday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereSunday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereThursday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereTuesday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereWednesday($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereWorkhourableId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereWorkhourableType($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Models\WorkHours withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Models\WorkHours withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours query()
     */
    class WorkHours extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Patient.
     *
     * @property int                                                                                                 $id
     * @property int|null                                                                                            $imported_medical_record_id
     * @property int                                                                                                 $user_id
     * @property int|null                                                                                            $ccda_id
     * @property int|null                                                                                            $care_plan_id
     * @property string|null                                                                                         $active_date
     * @property string|null                                                                                         $agent_name
     * @property string|null                                                                                         $agent_telephone
     * @property string|null                                                                                         $agent_email
     * @property string|null                                                                                         $agent_relationship
     * @property string|null                                                                                         $birth_date
     * @property string|null                                                                                         $ccm_status
     * @property string|null                                                                                         $consent_date
     * @property string|null                                                                                         $gender
     * @property \Carbon\Carbon|null                                                                                 $date_paused
     * @property \Carbon\Carbon|null                                                                                 $date_withdrawn
     * @property string|null                                                                                         $mrn_number
     * @property string|null                                                                                         $preferred_cc_contact_days
     * @property string|null                                                                                         $preferred_contact_language
     * @property string|null                                                                                         $preferred_contact_location
     * @property string|null                                                                                         $preferred_contact_method
     * @property string|null                                                                                         $preferred_contact_time
     * @property string|null                                                                                         $preferred_contact_timezone
     * @property string|null                                                                                         $registration_date
     * @property string|null                                                                                         $daily_reminder_optin
     * @property string|null                                                                                         $daily_reminder_time
     * @property string|null                                                                                         $daily_reminder_areas
     * @property string|null                                                                                         $hospital_reminder_optin
     * @property string|null                                                                                         $hospital_reminder_time
     * @property string|null                                                                                         $hospital_reminder_areas
     * @property \Carbon\Carbon                                                                                      $created_at
     * @property \Carbon\Carbon                                                                                      $updated_at
     * @property string|null                                                                                         $deleted_at
     * @property string                                                                                              $general_comment
     * @property int                                                                                                 $preferred_calls_per_month
     * @property string                                                                                              $last_successful_contact_time
     * @property int|null                                                                                            $no_call_attempts_since_last_success
     * @property string                                                                                              $last_contact_time
     * @property string                                                                                              $daily_contact_window_start
     * @property string                                                                                              $daily_contact_window_end
     * @property int|null                                                                                            $next_call_id
     * @property int|null                                                                                            $family_id
     * @property string|null                                                                                         $date_welcomed
     * @property \CircleLinkHealth\Customer\Entities\Family|null                                                     $family
     * @property mixed                                                                                               $address
     * @property mixed                                                                                               $city
     * @property mixed                                                                                               $first_name
     * @property mixed                                                                                               $last_name
     * @property mixed                                                                                               $state
     * @property mixed                                                                                               $zip
     * @property \CircleLinkHealth\Customer\Entities\PatientContactWindow[]|\Illuminate\Database\Eloquent\Collection $contactWindows
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                      $revisionHistory
     * @property \CircleLinkHealth\Customer\Entities\User                                                            $user
     * @property mixed location
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient enrolled()
     * @method static bool|null forceDelete()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient hasFamily()
     * @method static \Illuminate\Database\Query\Builder|\App\Patient onlyTrashed()
     * @method static bool|null restore()
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereActiveDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentRelationship($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereAgentTelephone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereBirthDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCarePlanId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCcdaId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCcmStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereConsentDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereCurMonthActivityTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyContactWindowEnd($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyContactWindowStart($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyReminderAreas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyReminderOptin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDailyReminderTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDatePaused($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDateWelcomed($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDateWithdrawn($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereFamilyId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereGender($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereGeneralComment($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHospitalReminderAreas($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHospitalReminderOptin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereHospitalReminderTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereImportedMedicalRecordId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereLastContactTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereLastSuccessfulContactTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereMrnNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereNextCallId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereNoCallAttemptsSinceLastSuccess($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredCallsPerMonth($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredCcContactDays($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactLanguage($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactLocation($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactMethod($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactTime($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient wherePreferredContactTimezone($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereRegistrationDate($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Patient whereUserId($value)
     * @method static \Illuminate\Database\Query\Builder|\App\Patient withTrashed()
     * @method static \Illuminate\Database\Query\Builder|\App\Patient withoutTrashed()
     * @mixin \Eloquent
     *
     * @property \Illuminate\Support\Carbon|null                   $paused_letter_printed_at
     * @property \Illuminate\Support\Carbon|null                   $date_unreachable
     * @property mixed                                             $last_call_status
     * @property \CircleLinkHealth\Customer\Entities\Location|null $location
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient byStatus($fromDate, $toDate)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient ccmStatus($status, $operator = '=')
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient filter(\App\Filters\QueryFilters $filters)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient query()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient whereDateUnreachable($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Patient wherePausedLetterPrintedAt($value)
     */
    class Patient extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\PhoneNumber.
     *
     * @property int                                      $id
     * @property int                                      $user_id
     * @property int                                      $location_id
     * @property string|null                              $number
     * @property string|null                              $extension
     * @property string|null                              $type
     * @property int                                      $is_primary
     * @property string                                   $created_at
     * @property string                                   $updated_at
     * @property string|null                              $deleted_at
     * @property \CircleLinkHealth\Customer\Entities\User $user
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereExtension($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereIsPrimary($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereLocationId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereNumber($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereType($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUserId($value)
     * @mixin \Eloquent
     *
     * @property string                                                                         $number_with_dashes
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PhoneNumber newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PhoneNumber newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PhoneNumber query()
     */
    class PhoneNumber extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\NurseMonthlySummary.
     *
     * @property int                 $id
     * @property int                 $nurse_id
     * @property string              $month_year
     * @property int                 $accrued_after_ccm
     * @property int                 $accrued_towards_ccm
     * @property int|null            $no_of_calls
     * @property int|null            $no_of_successful_calls
     * @property \Carbon\Carbon|null $created_at
     * @property \Carbon\Carbon|null $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereAccruedAfterCcm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereAccruedTowardsCcm($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereMonthYear($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereNoOfCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereNoOfSuccessfulCalls($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereNurseId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseMonthlySummary newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseMonthlySummary newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseMonthlySummary query()
     */
    class NurseMonthlySummary extends \Eloquent
    {
    }
}

namespace CircleLinkHealth\Customer\Entities{
    /**
     * CircleLinkHealth\Customer\Entities\Permission.
     *
     * @property int                                                                                 $id
     * @property string                                                                              $name
     * @property string|null                                                                         $display_name
     * @property string|null                                                                         $description
     * @property \Carbon\Carbon                                                                      $created_at
     * @property \Carbon\Carbon                                                                      $updated_at
     * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection $roles
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereDisplayName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|\App\Permission whereUpdatedAt($value)
     * @mixin \Eloquent
     *
     * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $users
     *
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Permission newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Permission newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Permission query()
     */
    class Permission extends \Eloquent
    {
    }
}

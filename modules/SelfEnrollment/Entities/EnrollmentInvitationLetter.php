<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter.
 *
 * @property int                             $id
 * @property int                             $practice_id
 * @property string                          $practice_logo_src
 * @property string                          $customer_signature_src
 * @property string                          $signatory_name
 * @property mixed                           $letter
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter query()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter whereCreatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter whereCustomerSignatureSrc($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter whereId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter whereLetter($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter wherePracticeId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter wherePracticeLogoSrc($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter whereSignatoryName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\EnrollmentInvitationLetter whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property mixed $ui_requests
 */
class EnrollmentInvitationLetter extends Model
{
    public const CUSTOMER_SIGNATURE_PIC        = 'customer signature (picture)';
    const DEPENDED_ON_PROVIDER                 = 'depended_on_leader_provider';
    const DEPENDED_ON_PROVIDER_GROUP           = 'depended_on_leader_provider_group';
    public const LOCATION_ENROLL_BUTTON        = 'location of enroll button on screen';
    public const LOCATION_ENROLL_BUTTON_SECOND = 'button on screen two';
    public const OPTIONAL_PARAGRAPH            = 'Optional Paragraph';
    public const OPTIONAL_TITLE                = 'Optional Title';
    public const PATIENT_FIRST_NAME            = 'patient first name';
    public const PRACTICE_NAME                 = 'Practice Name';
    public const PRACTICE_NUMBER               = 'Practice Specific CircleLink Number';
    public const PROVIDER_LAST_NAME            = 'provider last name';
    public const SIGNATORY_NAME                = 'Signatory Name';

    protected $fillable = [
        'practice_id',
        'practice_logo_src',
        'customer_signature_src',
        'signatory_name',
        'letter',
        'ui_requests',
    ];
}

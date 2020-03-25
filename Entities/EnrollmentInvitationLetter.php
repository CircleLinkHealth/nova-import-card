<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter.
 *
 * @property int                             $id
 * @property int                             $practice_id
 * @property string                          $practice_logo_src
 * @property string                          $customer_signature_src
 * @property string                          $signatory_name
 * @property mixed                           $letter
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter whereCustomerSignatureSrc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter whereLetter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter wherePracticeLogoSrc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter whereSignatoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EnrollmentInvitationLetter extends Model
{
    const CARE_AMBASSADOR_NUMBER = 'care ambassador phone number';
    const CUSTOMER_SIGNATURE_PIC = 'customer signature (picture)';
    const LOCATION_ENROLL_BUTTON = 'location of enroll button on screen';
    const PATIENT_FIRST_NAME     = 'patient first name';
    const PRACTICE_NAME          = 'Practice Name';
    const PROVIDER_LAST_NAME     = 'provider last name';
    const SIGNATORY_NAME         = 'Signatory Name';

    protected $fillable = [
        'practice_id',
        'practice_logo_src',
        'customer_signature_src',
        'signatory_name',
        'letter',
    ];
}

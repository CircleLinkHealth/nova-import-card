<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Validation\Rule;

class ImportPhones extends BaseCcdaImportTask
{
    /**
     * @var Enrollee
     */
    private $enrollee;
    /**
     * @var StringManipulation
     */
    private $str;

    public function __construct(User $patient, Ccda $ccda)
    {
        parent::__construct($patient, $ccda);
        $this->str = new StringManipulation();
    }

    protected function import()
    {
        $demographics = $this->transform($this->ccda->bluebuttonJson()->demographics);

        $pPhone = optional($this->enrollee())->primary_phone ?? $this->str->extractNumbers(
            $demographics['primary_phone']
        );
        $homePhone   = null;
        $mobilePhone = null;
        $workPhone   = null;

        //primary_phone` may be a phone number or phone type
        $primaryPhone = ! empty($pPhone)
            ? $this->str->formatPhoneNumberE164($pPhone)
            : $demographics['primary_phone'];

        $homeNumber = optional($this->enrollee())->home_phone ?? $demographics['home_phone'] ?? $primaryPhone;

        if ( ! empty($homeNumber)) {
            if (self::validatePhoneNumber($homeNumber)) {
                $number = $this->str->formatPhoneNumberE164($homeNumber);

                $makePrimary = 0 == strcasecmp(
                    $primaryPhone,
                    PhoneNumber::HOME
                ) || $primaryPhone == $number || ! $primaryPhone;

                $homePhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::HOME,
                    ],
                    [
                        'is_primary' => $makePrimary,
                    ]
                );

                /**
                 * Band-aid solution to avoid making all phones primary, if there is no primary phone.
                 * In `$makePrimary = strcasecmp($primaryPhone, PhoneNumber::HOME) == 0 || $primaryPhone == $number || ! $primaryPhone;`, `! $primaryPhone`
                 * would make all phones primary, if `! $primaryPhone`.
                 */
                if ($makePrimary) {
                    $primaryPhone = $number;
                }
            }
        }

        $mobileNumber = optional($this->enrollee())->cell_phone ?? $demographics['cell_phone'];
        if ( ! empty($mobileNumber)) {
            if (self::validatePhoneNumber($mobileNumber)) {
                $number = $this->str->formatPhoneNumberE164($mobileNumber);

                $makePrimary = 0 == strcasecmp($primaryPhone, PhoneNumber::MOBILE) || 0 == strcasecmp(
                    $primaryPhone,
                    'cell'
                ) || $primaryPhone == $number || ! $primaryPhone;

                $mobilePhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::MOBILE,
                    ],
                    [
                        'is_primary' => $makePrimary,
                    ]
                );
            }
        }

        $workNumber = $demographics['work_phone'];
        if ( ! empty($workNumber)) {
            if (self::validatePhoneNumber($mobileNumber)) {
                $number = $this->str->formatPhoneNumberE164($workNumber);

                $makePrimary = PhoneNumber::WORK == $primaryPhone || $primaryPhone == $number || ! $primaryPhone;

                $workPhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::WORK,
                    ],
                    [
                        'is_primary' => $makePrimary,
                    ]
                );
            }
        }

        if ( ! $primaryPhone) {
            $primaryPhone = empty($mobileNumber)
                ? empty($homeNumber)
                    ? empty($workNumber)
                        ? false
                        : $workPhone
                    : $homePhone
                : $mobilePhone;

            if ($primaryPhone) {
                $primaryPhone->setAttribute('is_primary', true);
                $primaryPhone->save();
            }

            if ( ! $primaryPhone && $demographics['primary_phone']) {
                PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $this->str->formatPhoneNumberE164($demographics['primary_phone']),
                        'type'    => PhoneNumber::HOME,
                    ],
                    [
                        'is_primary' => true,
                    ]
                );
            }
        } else {
            if (self::validatePhoneNumber($primaryPhone)) {
                $number = $this->str->formatPhoneNumberE164($primaryPhone);

                foreach (
                    [
                        PhoneNumber::HOME => $homePhone,
                        PhoneNumber::MOBILE => $mobilePhone,
                        PhoneNumber::WORK => $workPhone,
                    ] as $type => $phone
                ) {
                    if ( ! $phone) {
                        PhoneNumber::updateOrCreate(
                            [
                                'user_id' => $this->patient->id,
                                'number'  => $number,
                                'type'    => $type,
                            ],
                            [
                                'is_primary' => true,
                            ]
                        );

                        break;
                    }
                    if ($phone->number == $number) {
                        //number is already saved. bail
                        break;
                    }
                }
            }
        }
    }

    private function enrollee(): ?Enrollee
    {
        if ( ! $this->enrollee) {
            $this->enrollee = Enrollee::where(
                [
                    ['user_id', '=', $this->patient->id],
                    ['practice_id', '=', $this->patient->program_id],
                    ['first_name', '=', $this->patient->first_name],
                    ['last_name', '=', $this->patient->last_name],
                ]
            )->first();
        }

        return $this->enrollee;
    }

    private function transform(object $demographics): array
    {
        return $this->getTransformer()->demographics($demographics);
    }

    public static function validatePhoneNumber($phoneNumber)
    {
        $validator = \Validator::make(
            ['number' => $phoneNumber],
            [
                'number' => ['required', Rule::phone()->country(['US'])],
            ]
        );

        return $validator->passes();
    }
}

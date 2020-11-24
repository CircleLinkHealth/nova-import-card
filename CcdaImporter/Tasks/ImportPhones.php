<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use Illuminate\Validation\Rule;

class ImportPhones extends BaseCcdaImportTask
{
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

    protected function import()
    {
        $demographics = $this->transform($this->ccda->bluebuttonJson()->demographics);

        $pPhone = optional($this->enrollee)->primary_phone ?? extractNumbers(
            $demographics['primary_phone']
        );
        $homePhone   = null;
        $mobilePhone = null;
        $workPhone   = null;

        //primary_phone` may be a phone number or phone type
        $primaryPhone = ! empty($pPhone)
            ? formatPhoneNumberE164($pPhone)
            : $demographics['primary_phone'];

        $homeNumber = optional($this->enrollee)->home_phone ?? $demographics['home_phone'] ?? $primaryPhone;

        if ( ! empty($homeNumber)) {
            if (self::validatePhoneNumber($homeNumber)) {
                $number = formatPhoneNumberE164($homeNumber);

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

        $mobileNumber = optional($this->enrollee)->cell_phone ?? $demographics['cell_phone'];
        if ( ! empty($mobileNumber)) {
            if (self::validatePhoneNumber($mobileNumber)) {
                $number = formatPhoneNumberE164($mobileNumber);

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
                $number = formatPhoneNumberE164($workNumber);

                $makePrimary = PhoneNumber::ALTERNATE == $primaryPhone || $primaryPhone == $number || ! $primaryPhone;

                $workPhone = PhoneNumber::updateOrCreate(
                    [
                        'user_id' => $this->patient->id,
                        'number'  => $number,
                        'type'    => PhoneNumber::ALTERNATE,
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
                        'number'  => formatPhoneNumberE164($demographics['primary_phone']),
                        'type'    => PhoneNumber::HOME,
                    ],
                    [
                        'is_primary' => true,
                    ]
                );
            }
        } else {
            if (self::validatePhoneNumber($primaryPhone)) {
                $number = formatPhoneNumberE164($primaryPhone);

                foreach (
                    [
                        PhoneNumber::HOME => $homePhone,
                        PhoneNumber::MOBILE => $mobilePhone,
                        PhoneNumber::ALTERNATE => $workPhone,
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

    private function transform(object $demographics): array
    {
        return $this->getTransformer()->demographics($demographics);
    }
}

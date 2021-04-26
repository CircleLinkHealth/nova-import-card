<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Services;

use CircleLinkHealth\TwilioIntegration\Http\Requests\LookupResponse;
use CircleLinkHealth\TwilioIntegration\Models\TwilioLookup;
use NotificationChannels\Twilio\Twilio;
use Twilio\Exceptions\TwilioException;

/**
 * The purpose of this class is to introduce implementing TwilioInterface, so that we can easily mock Twilio SDK.
 * We are resolving TwilioInterface to this class.
 *
 * Class CustomTwilioService
 */
class CustomTwilioService extends Twilio implements TwilioInterface
{
    const LOOKUP_TYPE = 'carrier';

    public function lookup(string $e164PhoneNumber): LookupResponse
    {
        $dbResult = $this->lookupInDb($e164PhoneNumber);
        if ($dbResult) {
            return $dbResult;
        }

        $apiResult = $this->lookupApi($e164PhoneNumber);
        $this->storeResultInDb($apiResult);

        return $apiResult;
    }

    private function lookupApi(string $e164PhoneNumber): ?LookupResponse
    {
        try {
            $resp = $this->twilioService
                ->lookups
                ->v1
                ->phoneNumbers($e164PhoneNumber)
                ->fetch(['type' => self::LOOKUP_TYPE]);

            $result = LookupResponse::fromApiResponse($resp);
        } catch (TwilioException $e) {
            $result = LookupResponse::fromApiError($e164PhoneNumber, $e);
        }

        return $result;
    }

    private function lookupInDb(string $e164PhoneNumber): ?LookupResponse
    {
        $cpmFormatted = formatPhoneNumber($e164PhoneNumber);

        /** @var ?TwilioLookup $result */
        $result = TwilioLookup::whereIn('phone_number', [$e164PhoneNumber, $cpmFormatted])
            ->first();

        if ( ! $result) {
            return null;
        }

        return LookupResponse::fromDb($result);
    }

    private function storeResultInDb(LookupResponse $response)
    {
        TwilioLookup::updateOrCreate(
            [
                'phone_number' => $response->phoneNumber,
            ],
            [
                'is_mobile'         => $response->isMobile,
                'carrier'           => $response->carrierName,
                'api_error_code'    => $response->errorCode,
                'api_error_details' => $response->errorDetails,
            ]
        );
    }
}

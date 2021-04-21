<?php


namespace CircleLinkHealth\SelfEnrollment\Services;


use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use CircleLinkHealth\SelfEnrollment\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class LetterSignaturesConstructor
{
    /**
     * @param int $letterId
     * @return Collection
     */
    public function getSignaturesForCurrentLetter(EnrollmentInvitationLetterV2 $letter, User $patient): Collection
    {
        $signaturesMedia =  $letter->getMedia(EnrollmentInvitationLetterV2::MEDIA_COLLECTION_SIGNATURE_NAME);

        if ($signaturesMedia->isEmpty()){
            $message = "Could not find signatures in media table for self enrollment letter with id:[$letter->id].";
            Log::error($message);
            sendSlackMessage('#self_enrollment_logs', $message);
            return collect();
        }

        $signatures = $this->constructSignaturesCollection($signaturesMedia);

        if ($signatures->isEmpty()){
            $message = "constructSignaturesCollection() return empty collection for letter:[$letter->id].";
            Log::error($message);
            sendSlackMessage('#self_enrollment_logs', $message);
            return collect();
        }


        if ($signatures->count() === 1 &&  empty($signatures->first()['providers_under_same_signature'])){
            return $signatures;
        }

        $patientBillingProviderUser = $patient->billingProviderUser();
        $anAdminReviewingLetter = $patient->isAdmin();

        if ($anAdminReviewingLetter){
            $patientBillingProviderUser = $signatures->first()['provider'];
        }

        if (! $patientBillingProviderUser){
            Log::error("Billing Provider not found for enrollee with user_id [$patient->id]");
            return collect();
        }

        $signaturesForLetterView = collect();

        foreach ($signatures as $signature){
            if ((! empty($signature['providers_under_same_signature'])
                    && in_array($patientBillingProviderUser->id, $signature['providers_under_same_signature']))
                || intval($signature['provider_id']) === $patientBillingProviderUser->id){
                $signaturesForLetterView->push($signature);
            }
        }

        return $signaturesForLetterView;
    }

    private function constructSignaturesCollection(MediaCollection $signaturesMedia):Collection
    {
        $signatures = collect();
        foreach ($signaturesMedia as $signature){
            $collectedSignatures = [];

            if (! $signature->hasCustomProperty('provider_signature_id')) {
                return $signatures;
            }

            $providerId = $signature->custom_properties['provider_signature_id'];

            $collectedSignatures['signature_url'] = $signature->getUrl() ?? '';
            $provider = User::find($providerId);
            $collectedSignatures['provider'] = $provider;
            $collectedSignatures['provider_name'] = optional($provider)->display_name ?? '';
            $collectedSignatures['provider_id'] = $providerId;
            $collectedSignatures['provider_specialty'] = $provider ? Helpers::providerMedicalType($provider->suffix) : '';

            if ($signature->hasCustomProperty('providers_under_same_signature')){
                $collectedSignatures['providers_under_same_signature'] = $signature->custom_properties['providers_under_same_signature'];
            }

            if ($signature->hasCustomProperty('signatory_title_attributes')){
                $collectedSignatures['signatory_title_attributes'] = $signature->custom_properties['signatory_title_attributes'];
            }

            $signatures->push($collectedSignatures);
        }

        return $signatures;
    }
}
<?php


namespace CircleLinkHealth\SelfEnrollment\Services;


use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use CircleLinkHealth\SelfEnrollment\ValueObjects\LetterSignatureValueObject;
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


        if ($signatures->count() === 1 &&  empty($signatures->first()->getProvidersUnderSameSignature())){
            return $signatures;
        }

        $patientBillingProviderUser = $patient->billingProviderUser();
        $anAdminReviewingLetter = $patient->isAdmin();

        if ($anAdminReviewingLetter){
            $patientBillingProviderUser = $signatures->first()->signatoryProvider();
        }

        if (! $patientBillingProviderUser){
            Log::error("Billing Provider not found for enrollee with user_id [$patient->id]");
            return collect();
        }

        $signaturesForLetterView = collect();

        foreach ($signatures as $signature){
            if ((! empty($signature->getProvidersUnderSameSignature())
                    && in_array($patientBillingProviderUser->id, $signature->getProvidersUnderSameSignature()))
                || intval($signature->getProviderId()) === $patientBillingProviderUser->id){
                $signaturesForLetterView->push($signature);
            }
        }

        return $signaturesForLetterView;
    }

    private function constructSignaturesCollection(MediaCollection $signaturesMedia):Collection
    {
        $signatures = collect();
        /** @var Media $signature */
        foreach ($signaturesMedia as $signature){
            $signatureObjectsCollection = collect();

            if (! $signature->hasCustomProperty('provider_signature_id')) {
                return $signatures;
            }

            $providerId = $signature->custom_properties['provider_signature_id'];
            $provider = User::find($providerId);

            $signatureObjectsCollection->push(new LetterSignatureValueObject($signature, $provider));

            $signatures->push(...$signatureObjectsCollection);
        }

        return $signatures;
    }
}
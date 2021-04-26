<?php


namespace CircleLinkHealth\SelfEnrollment\ValueObjects;


use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Helpers;
use Illuminate\Support\Collection;

class LetterSignatureValueObject
{
    private Media $signature;
    private User $provider;
    private string $signatureUrl;
    private string $providerName;
    private string $providerSpecialty;
    private ?array $providersUnderSameSignature;
    private ?string $signatoryTitleAttributes;
    private ?int $providerId;

    /**
     * LetterSignatureValueObject constructor.
     * @param Media $signature
     * @param User $provider
     */
    public function __construct(Media $signature, User $provider)
    {
        $this->signature = $signature;
        $this->provider = $provider;
        $this->signatureUrl = $this->getSignatureUrl();
        $this->providerName = $this->getProviderName();
        $this->providerId = $this->getProviderId();
        $this->providerSpecialty = $this->getProviderSpecialty();
        $this->providersUnderSameSignature = $this->getProvidersUnderSameSignature();
        $this->signatoryTitleAttributes = $this->getSignatoryTitleAttributes();
    }

    public function getSignatoryTitleAttributes(): ?string
    {
        if (! $this->signature->hasCustomProperty('signatory_title_attributes')){
            return null;
        }

        return $this->signature->custom_properties['signatory_title_attributes'];
    }

    public function getSignatoryProvider(): ?User
    {
        return $this->provider;
    }

    public function getProviderId(): ?int
    {
        return optional($this->getSignatoryProvider())->id ?? null;
    }

    public function getSignatureUrl(): string
    {
        return $this->signature->getUrl() ?? '';
    }

    public function getProviderName(): string
    {
        return optional($this->getSignatoryProvider())->display_name ?? '';
    }

    public function getProviderSpecialty(): string
    {
        return $this->getSignatoryProvider() ? Helpers::providerMedicalType($this->getSignatoryProvider()->suffix) : '';
    }

    public function getProvidersUnderSameSignature(): ?array
    {
        if (! $this->signature->hasCustomProperty('providers_under_same_signature')){
            return [];
        }

        $signatureCustomProperties = $this->signature->custom_properties['providers_under_same_signature'];

        if (is_string($signatureCustomProperties)){
            $signatureCustomProperties = json_decode($signatureCustomProperties);
        }

        return collect($signatureCustomProperties)->map(function($signatoryId){
            return intval($signatoryId);
        })->toArray();
    }
}
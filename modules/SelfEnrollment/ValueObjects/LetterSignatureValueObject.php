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
    private string $signatoryTitleAttributes;
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
        $this->signatureUrl = $this->signatureUrl();
        $this->providerName = $this->providerName();
        $this->providerId = $this->providerId();
        $this->providerSpecialty = $this->providerSpecialty();
        $this->providersUnderSameSignature = $this->providersUnderSameSignature();
        $this->signatoryTitleAttributes = $this->signatoryTitleAttributes();
    }

    public function signatoryTitleAttributes(): string
    {
        if (! $this->signature->hasCustomProperty('signatory_title_attributes')){
            return '';
        }

        return $this->signature->custom_properties['signatory_title_attributes'];
    }

    public function signatoryProvider(): ?User
    {
        return $this->provider;
    }

    public function providerId(): ?int
    {
        return optional($this->signatoryProvider())->id ?? null;
    }

    public function signatureUrl(): string
    {
        return $this->signature->getUrl() ?? '';
    }

    public function providerName(): string
    {
        return optional($this->signatoryProvider())->display_name ?? '';
    }

    public function providerSpecialty(): string
    {
        return $this->signatoryProvider() ? Helpers::providerMedicalType($this->signatoryProvider()->suffix) : '';
    }

    public function providersUnderSameSignature(): ?array
    {
        if (! $this->signature->hasCustomProperty('providers_under_same_signature')){
            return [];
        }
        return $this->signature->custom_properties['providers_under_same_signature'];
    }
}
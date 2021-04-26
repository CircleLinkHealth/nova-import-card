<?php
namespace CircleLinkHealth\SelfEnrollment\ValueObjects;

use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use Illuminate\Support\Collection;


class PracticeLetterData
{
    /**
     * @var EnrollmentInvitationLetterV2
     */
    public string $body;
    public string $options;
    private ?string $logoUrl;

    private ?Collection $signatures;
    private Collection $mainSignatoryProvidersIds;
    private Collection $childSignatoryProvidersIds;

    private Collection $allSignatoryProvidersIds;


    /**
     * PracticeLetterData constructor.
     * @param string $body
     * @param string $options
     * @param string|null $logoUrl
     * @param Collection|null $signatures
     */
    public function __construct(string $body, string $options, ?string $logoUrl, ?Collection $signatures)
    {
        $this->body = $body;
        $this->options = $options;
        $this->logoUrl = $logoUrl;
        $this->signatures = $signatures;
        $this->mainSignatoryProvidersIds = $this->mainSignatoryProvidersIds();
        $this->childSignatoryProvidersIds = $this->childSignatoryProvidersIds();
        $this->allSignatoryProvidersIds = $this->allSignatoryProvidersIds();
    }

    /**
     * @return EnrollmentInvitationLetterV2|string
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function options()
    {
        return json_decode($this->options);
    }

    /**
     * @return string
     */
    public function logoPosition()
    {
        return $this->options()->logo_position ?? 'left';
    }

    /**
     * @return string
     */
    public function logoSize()
    {
        return "{$this->options()->logo_size}px";
    }

    /**
     * @return string
     */
    public function logoDistanceFromText()
    {
        return $this->options()->logo_distance_from_text ?
            "{$this->options()->logo_distance_from_text}px"
            : '70px';
    }


    /**
     * @return string
     */
    public function logoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @return Collection
     */
    public function signatures()
    {
        return $this->signatures;
    }


    public function mainSignatoryProvidersIds(): Collection
    {
        $providerIds = collect();

        $this->signatures()->each(function ($signature) use($providerIds){
            $providerId = $signature->providerId();
            if ($providerId){
                $providerIds->push($providerId);
            }
        });

        return $providerIds;
    }

    public function childSignatoryProvidersIds(): Collection
    {
        $childProviderIds = collect();

        $this->signatures()->each(function ($signature) use($childProviderIds){
            /** @var LetterSignatureValueObject $signature */
            $providersIds = $signature->providersUnderSameSignature();
            if (!empty($providersIds)){
                $childProviderIds->push($providersIds);
            }
        });

        return $childProviderIds;
    }

    public function allSignatoryProvidersIds(): Collection
    {
        $parentSignatoryIds = $this->mainSignatoryProvidersIds;
        $childSignatoryIds = $this->childSignatoryProvidersIds;

        if($parentSignatoryIds->isEmpty() && $childSignatoryIds->isEmpty()){
            return collect();
        }

        return $parentSignatoryIds->merge(...$childSignatoryIds)->unique();


    }
}
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
    /**
     * @var Collection|null
     */
    private ?Collection $signatures;


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
}
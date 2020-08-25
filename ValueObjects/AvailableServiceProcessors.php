<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use CircleLinkHealth\CcmBilling\Processors\Patient\AWV1;
use CircleLinkHealth\CcmBilling\Processors\Patient\AWV2;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM40;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM60;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class AvailableServiceProcessors implements Arrayable
{
    private ?AWV1 $awv1;

    private ?AWV2 $awv2;
    private ?BHI $bhi;

    private ?CCM $ccm;

    private ?CCM40 $ccm40;

    private ?CCM60 $ccm60;

    private ?PCM $pcm;

    public static function classMap(): array
    {
        return [
            CCM::class   => 'ccm',
            BHI::class   => 'bhi',
            CCM40::class => 'ccm40',
            CCM60::class => 'ccm60',
            PCM::class   => 'pcm',
            AWV1::class  => 'awv1',
            AWV2::class  => 'awv2',
        ];
    }

    public function getAwv1(): ?AWV1
    {
        return $this->awv1;
    }

    public function getAwv2(): ?AWV2
    {
        return $this->awv2;
    }

    public function getBhi(): ?BHI
    {
        return $this->bhi;
    }

    public function getCcm(): ?CCM
    {
        return $this->ccm;
    }

    public function getCcm40(): ?CCM40
    {
        return $this->ccm40;
    }

    public function getCcm60(): ?CCM60
    {
        return $this->ccm60;
    }

    public function getPcm(): ?PCM
    {
        return $this->pcm;
    }

    public static function push(array $serviceProcessors)
    {
        $self = new static();
        foreach ($serviceProcessors as $processor) {
            $func = 'set'.ucwords(self::classMap()[get_class($processor)]);
            $self->$func($processor);
        }

        return $self;
    }

    public function setAwv1(?AWV1 $awv1): void
    {
        $this->awv1 = $awv1;
    }

    public function setAwv2(?AWV2 $awv2): void
    {
        $this->awv2 = $awv2;
    }

    public function setBhi(?BHI $bhi): void
    {
        $this->bhi = $bhi;
    }

    public function setCcm(?CCM $ccm): void
    {
        $this->ccm = $ccm;
    }

    public function setCcm40(?CCM40 $ccm40): void
    {
        $this->ccm40 = $ccm40;
    }

    public function setCcm60(?CCM60 $ccm60): void
    {
        $this->ccm60 = $ccm60;
    }

    public function setPcm(?PCM $pcm): void
    {
        $this->pcm = $pcm;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter([
            $this->awv1 ?? null,
            $this->awv2 ?? null,
            $this->ccm ?? null,
            $this->ccm40 ?? null,
            $this->ccm60 ?? null,
            $this->pcm ?? null,
            $this->bhi ?? null,
        ]);
    }

    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\DTO;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class PracticePullFileInGoogleDrive implements Arrayable
{
    protected ?Carbon $dispatchedAt = null;
    protected ?Carbon $finishedProcessingAt = null;
    protected string $importer;
    protected string $name;
    protected string $path;
    protected string $typeOfData;

    public function __construct(string $name, string $path, string $typeOfData, string $importer)
    {
        $this->name         = $name;
        $this->path         = $path;
        $this->dispatchedAt = now();
        $this->typeOfData   = $typeOfData;
        $this->importer     = $importer;
    }

    /**
     * @return Carbon|\Illuminate\Support\Carbon
     */
    public function getDispatchedAt()
    {
        return $this->dispatchedAt;
    }

    public function getFinishedProcessingAt(): Carbon
    {
        return $this->finishedProcessingAt;
    }

    public function getImporter(): string
    {
        return $this->importer;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function getFileNameWithoutExtension(): string
    {
        return pathinfo($this->getName())['filename'];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTypeOfData(): string
    {
        return $this->typeOfData;
    }

    public function setFinishedProcessingAt(Carbon $finishedProcessingAt): PracticePullFileInGoogleDrive
    {
        $this->finishedProcessingAt = $finishedProcessingAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'dispatchedAt'         => optional($this->dispatchedAt)->toDateTimeString(),
            'finishedProcessingAt' => optional($this->finishedProcessingAt)->toDateTimeString(),
            'importer'             => $this->importer,
            'name'                 => $this->name,
            'path'                 => $this->path,
            'typeOfData'           => $this->typeOfData,
        ];
    }
}

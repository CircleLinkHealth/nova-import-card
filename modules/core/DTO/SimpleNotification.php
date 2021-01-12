<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\DTO;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class SimpleNotification implements Arrayable
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var string|null
     */
    protected $ccdaAttachmentPath;

    /**
     * @var string|null
     */
    protected $fileName;
    /**
     * @var string|null
     */
    protected $filePath;

    /**
     * @var User|null
     */
    protected $patient;

    /**
     * @var string
     */
    protected $subject;

    public function getBody(): string
    {
        return $this->body;
    }

    public function getCcdaAttachmentPath(): ?string
    {
        return $this->ccdaAttachmentPath;
    }

    public function getFileName(): ?string
    {
        if ( ! $this->fileName && Str::contains($this->filePath, '/')) {
            $this->fileName = substr($this->filePath, strrpos($this->filePath, '/') + 1);
        }

        return $this->fileName;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string|null $body
     *
     * @return SimpleNotification
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return SimpleNotification
     */
    public function setCcdaAttachmentPath(string $ccdaAttachmentPath = null)
    {
        $this->ccdaAttachmentPath = $ccdaAttachmentPath;

        return $this;
    }

    /**
     * @param string|null $fileName
     *
     * @return SimpleNotification
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return SimpleNotification
     */
    public function setFilePath(string $filePath = null)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return SimpleNotification
     */
    public function setPatient(User $patient = null)
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @return SimpleNotification
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}

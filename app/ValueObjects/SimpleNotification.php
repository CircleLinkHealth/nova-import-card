<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

use CircleLinkHealth\Customer\Entities\User;

class SimpleNotification
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

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string|null
     */
    public function getCcdaAttachmentPath(): ?string
    {
        return $this->ccdaAttachmentPath;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @return User|null
     */
    public function getPatient(): ?User
    {
        return $this->patient;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param mixed $body
     *
     * @return SimpleNotification
     */
    public function setBody($body): string
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param mixed $ccdaAttachmentPath
     *
     * @return SimpleNotification
     */
    public function setCcdaAttachmentPath($ccdaAttachmentPath): ?string
    {
        $this->ccdaAttachmentPath = $ccdaAttachmentPath;

        return $this;
    }

    /**
     * @param mixed $fileName
     *
     * @return SimpleNotification
     */
    public function setFileName($fileName): ?string
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @param mixed $filePath
     *
     * @return SimpleNotification
     */
    public function setFilePath($filePath): ?string
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @param mixed $patient
     *
     * @return SimpleNotification
     */
    public function setPatient($patient): ?User
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @param mixed $subject
     *
     * @return SimpleNotification
     */
    public function setSubject($subject): string
    {
        $this->subject = $subject;

        return $this;
    }
}

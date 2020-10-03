<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Patientapi\ValueObjects;

class CcdProblemInput
{
    protected ?int $ccdProblemId;
    protected ?int $cpmProblemId;
    protected ?string $icd10;
    protected ?string $instruction;
    protected ?bool $isMonitored;
    protected ?string $name;
    protected ?int $userId;

    public function fromRequest(array $input): self
    {
        return $this->setCpmProblemId($input['cpm_problem_id'] ?? null)
            ->setIcd10($input['icd10'] ?? null)
            ->setInstruction($input['instruction'] ?? null)
            ->setIsMonitored($input['is_monitored'] ?? null)
            ->setName($input['name'] ?? null);
    }

    /**
     * @return mixed
     */
    public function getCcdProblemId(): ?int
    {
        return $this->ccdProblemId;
    }

    /**
     * @return mixed
     */
    public function getCpmProblemId(): ?int
    {
        return $this->cpmProblemId;
    }

    /**
     * @return mixed
     */
    public function getIcd10(): ?string
    {
        return $this->icd10;
    }

    /**
     * @return mixed
     */
    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    /**
     * @return mixed
     */
    public function getIsMonitored(): ?bool
    {
        return $this->isMonitored;
    }

    /**
     * @return mixed
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param mixed $ccdProblemId
     */
    public function setCcdProblemId($ccdProblemId): self
    {
        $this->ccdProblemId = $ccdProblemId;

        return $this;
    }

    /**
     * @param mixed $cpmProblemId
     */
    public function setCpmProblemId($cpmProblemId): self
    {
        $this->cpmProblemId = $cpmProblemId;

        return $this;
    }

    /**
     * @param mixed $icd10
     */
    public function setIcd10($icd10): self
    {
        $this->icd10 = $icd10;

        return $this;
    }

    /**
     * @param mixed $instruction
     */
    public function setInstruction($instruction): self
    {
        $this->instruction = $instruction;

        return $this;
    }

    /**
     * @param mixed $isMonitored
     */
    public function setIsMonitored($isMonitored): self
    {
        $this->isMonitored = $isMonitored;

        return $this;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}

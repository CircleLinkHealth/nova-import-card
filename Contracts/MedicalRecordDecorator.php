<?php


namespace CircleLinkHealth\Eligibility\Contracts;


use CircleLinkHealth\Eligibility\Entities\EligibilityJob;

interface MedicalRecordDecorator
{
    public function decorate(EligibilityJob $job):EligibilityJob;
}
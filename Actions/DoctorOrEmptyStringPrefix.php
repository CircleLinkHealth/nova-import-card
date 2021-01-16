<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Actions;

use Illuminate\Support\Str;

class DoctorOrEmptyStringPrefix
{
    private string $fullName;
    private ?string $specialty;

    public function __construct(string $fullName, ?string $specialty)
    {
        $this->fullName  = $fullName;
        $this->specialty = $specialty;
    }

    public function __toString()
    {
        if (in_array($this->sanitizedSpecialty(), $this->doctorSpecialties()) && ! Str::startsWith(strtolower($this->fullName), 'dr.')) {
            return 'Dr. ';
        }

        return '';
    }

    private function doctorSpecialties()
    {
        return [
            'D.O',
            'DO',
            'Dr',
            'MD',
            'Addiction Psychiatry',
            'Adolescent Medicine',
            'Allergy & Immunology',
            'Anesthesiology',
            'Back surgeon',
            'Bariatric',
            'Cardiologist',
            'Cardiology',
            'Cardiovascular Disease',
            'Clinical Neurophysiology',
            'Colon & Rectal Surgery',
            'D.O',
            'Dermatology',
            'Dermatopathology',
            'DR',
            'Ears, Nose, Throat (ENT)',
            'Endocrinology, Diabetes & Metabolism',
            'Family Medicine',
            'Family Practice',
            'Foot & Ankle Orthopaedics',
            'Gastroenterology',
            'Glaucoma Specialist',
            'GYN',
            'gynecology',
            'Hematology',
            'Hematology & Oncology',
            'Infectious Disease',
            'Internal Medicine',
            'MD',
            'Nephrology',
            'Neurological Surgery',
            'Neurology',
            'Obstetrics & Gynecology',
            'Oncologist',
            'Oncologists',
            'Oncology',
            'Ophthalmology',
            'Opthamology',
            'Orthopaedic Sports Medicine',
            'Orthopaedic Surgery',
            'Orthopaedic Surgery of the Spine',
            'Orthopedic surgeon',
            'Otolaryngology',
            'pain management',
            'Pain mananagement',
            'Pain Medicine',
            'Pediatric Pulmonology',
            'Podiatrist',
            'Primary Care',
            'Psychiatry',
            'Pulmonary Disease',
            'Pulmonary Disease & Critical Care Medicine',
            'Pulmonolgy',
            'Pulmonologist',
            'Pulmonology',
            'Radiation Oncology',
            'Rheumatolgy',
            'Rheumatology',
            'Spine surgeon',
            'Surgeon',
            'Surgery-General',
            'Thoracic Surgery',
            'urogynecology',
            'Urologist',
            'Urology',
            'vascular',
            'Vascular & Interventional Radiology',
            'Vascular Surgery',
        ];
    }

    private function sanitizedSpecialty()
    {
        return ltrim(strtoupper($this->specialty), "\n ");
    }
}

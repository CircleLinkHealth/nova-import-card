<?php

namespace App\CLH\Contracts\CCD;

interface ParsingStrategy
{
    public function parse(
        \App\Models\MedicalRecords\Ccda $ccda,
        ValidationStrategy $validator = null
    );
}

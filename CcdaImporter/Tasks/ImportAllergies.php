<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use Illuminate\Support\Facades\DB;
use  Illuminate\Support\Str;

class ImportAllergies extends BaseCcdaImportTask
{
    protected function import()
    {
        $this->patient->loadMissing('ccdAllergies');

        $allergies = collect($this->ccda->bluebuttonJson()->allergies ?? [])->unique('allergen.name')->map(function ($allergy) {
            $new = $this->transform($allergy);

            if ( ! $this->validate($new)) {
                return null;
            }

            if (empty($new['allergen_name'])) {
                return null;
            }

            if (Str::contains(strtolower($new['allergen_name']), 'no known')) {
                return null;
            }

            if ($this->patient->ccdAllergies->filter(function ($allergy) use ($new) {
                return str_replace(' ', '', strtolower($allergy->allergen_name)) === str_replace(' ', '', strtolower($new['allergen_name']));
            })->isNotEmpty()) {
                return null;
            }

            $createdAt = now()->toDateTimeString();

            return[
                'patient_id'    => $this->patient->id,
                'allergen_name' => ucfirst($new['allergen_name']),
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        })->filter();

        if ($allergies->isNotEmpty()) {
            DB::table('ccd_allergies')->insert($allergies->all());
        }
    }

    private function transform(object $allergy): array
    {
        return $this->getTransformer()->allergy($allergy);
    }
}

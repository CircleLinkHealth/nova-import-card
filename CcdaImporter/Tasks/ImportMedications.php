<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use App\MedicationGroupsMap;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\ConsolidatesMedicationInfo;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\Medication;

class ImportMedications extends BaseCcdaImportTask
{
    use ConsolidatesMedicationInfo;

    protected function import()
    {
        $medicationGroups = [];

        collect($this->ccda->bluebuttonJson()->medications ?? [])->each(
            function ($medication) use (&$medicationGroups) {
                $new = (array) $this->consolidateMedicationInfo((object) $this->transform($medication));

                if ($this->containsSigKeywords($new['cons_name'])) {
                    return null;
                }

                if ( ! $this->validate($new)) {
                    return null;
                }

                if ( ! $new['cons_name'] && ! $new['cons_text']) {
                    return null;
                }

                $sig = ucfirst(
                    (new StringManipulation())->stringDiff(
                        $new['cons_name'],
                        $new['cons_text']
                    )
                );

                $medicationGroupId = MedicationGroupsMap::getGroup($new['cons_name']) ?? MedicationGroupsMap::getGroup($sig);

                $ccdMedication = Medication::updateOrCreate(
                    [
                        'patient_id' => $this->patient->id,
                        'name'       => $new['cons_name'],
                        'sig'        => $sig,
                    ],
                    [
                        'medication_group_id' => $medicationGroupId,
                        'code'                => $new['cons_code'],
                        'code_system'         => $new['cons_code_system'],
                        'code_system_name'    => $new['cons_code_system_name'],
                        'ccda_id'             => $this->ccda->id,
                    ]
                );

                if ($medicationGroupId) {
                    $medicationGroups[] = $medicationGroupId;
                }
            }
        );

        $this->patient->load('ccdMedications');

        $unique = $this->patient->ccdMedications->unique('name')->pluck('id')->all();

        $deleted = $this->patient->ccdMedications()->whereNotIn('id', $unique)->delete();

        $this->patient->cpmMedicationGroups()->sync(array_filter($medicationGroups));

        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
            ->first();

        if ( ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
    }

    private function transform(object $medication): array
    {
        return $this->getTransformer()->medication($medication);
    }
}

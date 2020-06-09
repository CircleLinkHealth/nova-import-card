<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use App\MedicationGroupsMap;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\FiresImportingHooks;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\ConsolidatesMedicationInfo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportMedications extends BaseCcdaImportTask
{
    use ConsolidatesMedicationInfo;
    use FiresImportingHooks;
    const HOOK_IMPORTING_MEDICATIONS = 'IMPORTING_MEDICATIONS';

    protected function import()
    {
        $this->fireImportingHook(self::HOOK_IMPORTING_MEDICATIONS, $this->patient, $this->ccda, []);

        $this->patient->loadMissing('ccdMedications');
        $this->patient->loadMissing('cpmMedicationGroups');

        $medicationGroups = [];

        $medications = $this->processCollection(collect($this->getRawMedications()), $medicationGroups);

        if ($this->patient->ccdMedications->isEmpty() && $medications->isEmpty()) {
            $medications = $this->processCollection(collect($this->ccda->bluebuttonJson()->medications ?? []), $medicationGroups);
        }

        if ( ! empty($medicationGroups = array_filter($medicationGroups))) {
            DB::table('cpm_medication_groups_users')->insert($medicationGroups);
        }

        if ($medications->isNotEmpty()) {
            DB::table('ccd_medications')->insert($medications->all());
        }
    }

    private function getMedsFromOtherCcda(): array
    {
        $otherMeds = [];

        $this->ccda->queryForOtherCcdasForTheSamePatient()
            ->chunkById(10, function ($otherCcdas) use (&$otherMeds) {
                foreach ($otherCcdas as $otherCcda) {
                    $newMeds = $otherCcda->bluebuttonJson()->medications ?? [];

                    if ( ! empty($newMeds)) {
                        $data = $this->ccda->bluebuttonJson();
                        $data->medications = $newMeds;
                        $this->ccda->json = json_encode($data);
                        $this->ccda->save();

                        $otherMeds = $this->ccda->bluebuttonJson()->medications;

                        //break chunking
                        return false;
                    }
                }
            });

        return $otherMeds;
    }

    private function getRawMedications(): array
    {
        if (empty($meds = $this->ccda->bluebuttonJson()->medications ?? [])) {
            return $this->getMedsFromOtherCcda();
        }

        return $meds;
    }

    private function process(array $newMed, array &$medicationGroups): ?array
    {
        if ( ! $newMed['cons_name'] && ! $newMed['cons_text']) {
            return null;
        }

        if ($this->containsSigKeywords($newMed['cons_name'])) {
            return null;
        }

        if ($this->patient->ccdMedications->contains('name', '=', $newMed['cons_name'])) {
            return null;
        }

        if ( ! $this->validate($newMed)) {
            return null;
        }

        $sig = ucfirst(
            (new StringManipulation())->stringDiff(
                $newMed['cons_name'],
                $newMed['cons_text']
            )
        );

        $medGroupId = MedicationGroupsMap::getGroup($newMed['cons_name']) ?? MedicationGroupsMap::getGroup($sig);

        if (is_numeric($medGroupId) && ! in_array($medGroupId, $medicationGroups)) {
            $groupCreatedAt = now()->toDateTimeString();

            array_push($medicationGroups, [
                'patient_id'              => $this->patient->id,
                'cpm_medication_group_id' => $medGroupId,
                'created_at'              => $groupCreatedAt,
                'updated_at'              => $groupCreatedAt,
            ]);
        }

        $medCreatedAt = now()->toDateTimeString();

        return [
            'patient_id'          => $this->patient->id,
            'name'                => ucwords(strtolower($newMed['cons_name'])),
            'sig'                 => $sig,
            'medication_group_id' => $medGroupId,
            'code'                => $newMed['cons_code'],
            'code_system'         => $newMed['cons_code_system'],
            'code_system_name'    => $newMed['cons_code_system_name'],
            'ccda_id'             => $this->ccda->id,
            'created_at'          => $medCreatedAt,
            'updated_at'          => $medCreatedAt,
        ];
    }

    private function processCollection(Collection $rawMedications, array &$medicationGroups): Collection
    {
        return $rawMedications->map(function (object $medication) {
            return (array) $this->consolidateMedicationInfo((object) $this->transform($medication));
        })->unique('cons_name')
            ->transform(
                function ($medication) use (&$medicationGroups) {
                    return $this->process($medication, $medicationGroups);
                }
            )->filter();
    }

    private function transform(object $medication): array
    {
        return $this->getTransformer()->medication($medication);
    }
}

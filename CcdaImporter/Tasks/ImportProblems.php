<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use App\Constants;
use App\Importer\Section\Validators\NameNotNull;
use App\Importer\Section\Validators\ValidStatus;
use CircleLinkHealth\ConditionCodeLookup\Console\Commands\LookupCondition;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\GetProblemInstruction;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\FiresImportingHooks;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\ConsolidatesProblemInfo;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportProblems extends BaseCcdaImportTask
{
    use ConsolidatesProblemInfo;
    use FiresImportingHooks;
    const IMPORTING_PROBLEM_INSTRUCTIONS = 'IMPORTING_PROBLEM_INSTRUCTIONS';
    /**
     * @var Collection
     */
    private $cpmProblems;

    protected function import()
    {
        $this->cpmProblems = \Cache::remember(
            'all_cpm_problems',
            2,
            function () {
                return CpmProblem::all();
            }
        );

        $this->processProblems()->each(
            function ($problemCollection, $problemType) use (&$medicationGroups) {
                $problemCollection->each(function ($problem) use (&$medicationGroups, $problemType) {
                    if ( ! array_key_exists('attributes', $problem) || 'do_not_import' === $problemType) {
                        return false;
                    }

                    $new = $problem['attributes'];

                    $instruction = $this->getInstruction($problem);

                    $ccdProblem = Problem::updateOrCreate(
                        [
                            'name'           => $new['name'],
                            'patient_id'     => $this->patient->id,
                            'cpm_problem_id' => $new['cpm_problem_id'],
                        ],
                        [
                            'ccda_id'            => $this->ccda->id,
                            'is_monitored'       => (bool) $new['cpm_problem_id'],
                            'cpm_instruction_id' => optional($instruction)->id ?? null,
                        ]
                    );

                    if (array_key_exists('itemLog', $problem) && array_key_exists('codes', $problem['itemLog'])) {
                        collect($problem['itemLog']['codes'])->where('code', '!=', null)->where('code', '!=', 'null')->where('code', '!=', '')->each(
                            function ($codeLog) use ($ccdProblem) {
                                ProblemCode::updateOrCreate(
                                    [
                                        'problem_id' => $ccdProblem->id,
                                        'code'       => $codeLog['code'],
                                    ],
                                    [
                                        'code_system_name' => $codeLog['code_system_name'],
                                        'code_system_oid'  => $codeLog['code_system_oid'],
                                    ]
                                );
                            }
                        );
                    }
                });
            }
        );

        $this->patient->load('ccdProblems');

        $unique = $this->patient->ccdProblems->unique('name')->pluck('id')->all();

        $deleted = $this->patient->ccdProblems()->whereNotIn('id', $unique)->delete();

        $misc = CpmMisc::whereName(CpmMisc::OTHER_CONDITIONS)
            ->first();

        if ( ! $this->hasMisc($this->patient, $misc)) {
            $this->patient->cpmMiscs()->attach(optional($misc)->id);
        }
    }

    private function fetchNamesFromApi(Collection &$problemsGroups)
    {
        return $problemsGroups->transform(function ($problem) {
            if ((new NameNotNull())->isValid($problem) || ! $problem['code']) {
                return $problem;
            }

            $lookup = LookupCondition::lookup($problem['code'], 'any');
    
            $problem['name'] = $lookup['name'];
            $problem['code_system_name'] = $lookup['type'];
            $problem['codes'] = collect($problem['codes'] ?? [])->transform(function ($code) use ($lookup, $problem){
                if ($code['code'] == $problem['code']) {
                    $code['code_system_name'] = $lookup['type'];
                    $code['name'] = $lookup['name'];
                }
                
                return $code;
            })->all();

            return $problem;
        });
    }

    private function getCpmProblem($itemLog, $problemName)
    {
        if ( ! validProblemName($problemName)) {
            return null;
        }

        $codes = collect($itemLog['codes'] ?? [])->pluck('code')->filter()->values()->all();

        $problemMap = SnomedToCpmIcdMap::with('cpmProblem')
            ->has('cpmProblem')
            ->where(
                function ($q) use ($codes) {
                    $q->whereIn(Constants::ICD9, $codes)
                        ->where(Constants::ICD9, '!=', '')
                        ->whereNotNull(Constants::ICD9);
                }
            )
            ->orWhere(
                function ($q) use ($codes) {
                    $q->whereIn(Constants::ICD10, $codes)
                        ->where(Constants::ICD10, '!=', '')
                        ->whereNotNull(Constants::ICD10);
                }
            )
            ->orWhere(
                function ($q) use ($codes) {
                    $q->whereIn(Constants::SNOMED, $codes)
                        ->where(Constants::SNOMED, '!=', '')
                        ->whereNotNull(Constants::SNOMED);
                }
            )
            ->first();

        if ($problemMap) {
            return $problemMap->cpmProblem;
        }

        // Try to match keywords
        foreach ($this->cpmProblems as $cpmProblem) {
            //Do not perform keyword matching if name is just Cancer
            //https://circlelinkhealth.atlassian.net/browse/CPM-108
            if (0 === strcasecmp($problemName, 'cancer')) {
                break;
            }

            foreach (array_filter(array_merge(explode(',', $cpmProblem->contains), [$cpmProblem->name])) as $keyword) {
                if ( ! $keyword || empty($keyword)) {
                    continue;
                }

                $keyword = trim($keyword);

                if (Str::contains(strtolower($problemName), strtolower($keyword))) {
                    return $cpmProblem;
                }
            }
        }
    }

    private function getInstruction($newProblem)
    {
        $instructions = $this->fireImportingHook(
            self::IMPORTING_PROBLEM_INSTRUCTIONS,
            $this->patient,
            $this->ccda,
            $newProblem
        );

        if (is_null($instructions)) {
            return (new GetProblemInstruction($this->patient, $this->ccda))->run();
        }

        return $instructions;
    }

    private function majorityHasName(Collection $problemsGroups): bool
    {
        $haveName = $problemsGroups->reject(
            function (array $p) {
                if ( ! (new NameNotNull())->isValid($p)) {
                    return true;
                }
                $vs = new ValidStatus();

                if ( ! $vs->shouldValidate($p)) {
                    return false;
                }

                return ! $vs->isValid($p);
            }
        )->count();

        $haveCode = $problemsGroups->reject(
            function (array $p) {
                return empty($this->consolidateProblemInfo((object) $p)->cons_code);
            }
        )->filter()->count();

        return $haveName > $haveCode;
    }

    private function processProblems()
    {
        $problemsGroups = collect($this->ccda->bluebuttonJson()->problems ?? [])->map(
            function ($problem) use (&$medicationGroups) {
                return $this->transform($problem);
            }
        );

        $shouldValidateName = $this->majorityHasName($problemsGroups);

        if ( ! $shouldValidateName) {
            $problemsGroups     = $this->fetchNamesFromApi($problemsGroups);
            $shouldValidateName = $this->majorityHasName($problemsGroups);
        }

        if ($shouldValidateName) {
            $problemsGroups = $this->rejectProblemsWithoutName($problemsGroups);
        }

        return $problemsGroups->mapToGroups(
            function ($itemLog) use (
                $shouldValidateName
            ) {
                if ($shouldValidateName && ! $this->validate($itemLog)) {
                    return ['do_not_import' => $itemLog];
                }

                /**
                 * Check if the information is in the Translation Section of BB.
                 */
                $problemCodes = $this->consolidateProblemInfo((object) $itemLog);

                if ( ! validProblemName($problemCodes->cons_name)) {
                    return ['do_not_import' => $itemLog];
                }

                $cpmProblem = $this->getCpmProblem($itemLog, $problemCodes->cons_name);
                $cpmProblemId = optional($cpmProblem)->id;

                //if problem is Diabetes and string contains 2, we assume diabetes type 2
                if (1 == $cpmProblemId && Str::contains($problemCodes->cons_name, ['2'])) {
                    $cpmProblem = $this->cpmProblems->firstWhere(
                        'name',
                        'Diabetes Type 2'
                    );
                }
                //if problem is Diabetes and string contains 1, we assume diabetes type 1
                elseif (1 == $cpmProblemId && Str::contains(
                    $problemCodes->cons_name,
                    ['1']
                )) {
                    $cpmProblem = $this->cpmProblems->firstWhere(
                        'name',
                        'Diabetes Type 1'
                    );
                }

                $problem = [
                    'attributes' => [
                        'name'           => $problemCodes->cons_name,
                        'cpm_problem_id' => $cpmProblemId,
                    ],
                    'is_behavioral' => optional($cpmProblem)->is_behavioral,
                    'itemLog'       => $itemLog,
                ];

                if ($cpmProblem) {
                    return ['monitored' => $problem];
                }

                return ['not_monitored' => $problem];
            }
        );
    }

    private function rejectProblemsWithoutName(Collection $problemsGroups): Collection
    {
        return $problemsGroups->unique(
            function ($itemLog) {
                $itemLog = (object) $itemLog;
                $name = $itemLog->name ?? $itemLog->reference_title ?? $itemLog->translation_name ?? null;

                return empty($name)
                    ? false
                    : $name;
            }
        )
            ->values();
    }

    private function transform(object $problem): array
    {
        return array_merge(
            $this->getTransformer()->problem($problem),
            ['codes' => $this->getTransformer()->problemCodes($problem)]
        );
    }
}

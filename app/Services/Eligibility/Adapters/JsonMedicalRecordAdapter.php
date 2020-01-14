<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Eligibility\Adapters;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use App\Services\Eligibility\Entities\MedicalRecord;
use CircleLinkHealth\Eligibility\ValidatesEligibility;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Seld\JsonLint\JsonParser;

class JsonMedicalRecordAdapter
{
    use CircleLinkHealth\Eligibility\ValidatesEligibility;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var MedicalRecord|null
     */
    private $medicalRecord;
    /**
     * A json string that is the source data.
     *
     * @var string
     */
    private $source;

    /**
     * @var Collection
     */
    private $validatedData;

    /**
     * @var MessageBag|null
     */
    private $validationErrors;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     * @param \CircleLinkHealth\Eligibility\Entities\EligibilityBatch $eligibilityBatch
     *
     * @return \CircleLinkHealth\Eligibility\Entities\EligibilityJob|null
     */
    public function createEligibilityJob(EligibilityBatch $eligibilityBatch): ?EligibilityJob
    {
        //hack to fix Lakhsmi's broken json for River city list from september 2018
        if (119 == $eligibilityBatch->practice_id) {
            $this->source = str_replace('1/2"', '1/2', $this->source);
            $this->source = str_replace('n\a', 'n/a', $this->source);
        }

        if ( ! $this->isValid()) {
            return null;
        }

        $hash = $this->getKey($eligibilityBatch);

        if ($eligibilityBatch->shouldSafeReprocess()) {
            $job = EligibilityJob::updateOrCreate([
                'batch_id' => $eligibilityBatch->id,
                'hash'     => $hash,
            ], [
                'data'     => $this->validatedData->all(),
                'messages' => null, //reset since we are re-processing
                'outcome'  => null, //reset since we are re-processing
                'status'   => 0, //reset since we are re-processing
            ]);
        } else {
            $job = EligibilityJob::create([
                'batch_id' => $eligibilityBatch->id,
                'hash'     => $hash,
                'data'     => $this->validatedData->all(),
            ]);
        }

        return $job;
    }

    /**
     * @return MedicalRecord|null
     */
    public function createMedicalRecord(): ?MedicalRecord
    {
    }

    /**
     * @return Collection
     */
    public function decode()
    {
        $isJson = is_json($this->source);

        if ( ! $isJson) {
            $parser = new JsonParser();

            try {
                $parser->parse($this->source, JsonParser::DETECT_KEY_CONFLICTS);
            } catch (\Exception $e) {
                \Log::debug('NOT VALID JSON: '.json_encode($e->getDetails()).$this->source);
            }

            return collect();
        }

        $decoded = json_decode($this->source, true);

        return collect($decoded);
    }

    /**
     * @return mixed
     */
    public function getMedicalRecord()
    {
        return $this->medicalRecord;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if ( ! is_null($this->isValid)) {
            return $this->isValid;
        }

        $coll = $this->decode();

        if ($coll->isEmpty()) {
            return false;
        }

        // We need the validation to pass at the moment because we need the eligibility job to be created.
        // The validation will run at welcomeCallListGenerator and store the errors on the eligibility job.
//        $this->isValid = $this->validateRow($coll->all())->passes();
        $this->isValid = true;
        if ($this->isValid) {
            $this->validatedData = $coll;
        }

        return $this->isValid;
    }

    private function getKey(EligibilityBatch $eligibilityBatch)
    {
        $key = $eligibilityBatch->practice->name
               .$this->validatedData->get('first_name')
               .$this->validatedData->get('last_name');

        $dob = null;

        try {
            $dob = Carbon::parse($this->validatedData->get('date_of_birth'))->toDateString();
        } catch (\Exception $e) {
            \Log::debug("Could not parse `date_of_birth`. Value {$this->validatedData->get('date_of_birth')}. Key: `${key}`. {$e->getMessage()}, {$e->getCode()}. Source json string: `{$this->source}`");
        }

        return $key
               .$dob ?? $this->validatedData->get('date_of_birth');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 9/11/18
 * Time: 2:26 PM
 */

namespace App\Services\Eligibility\Adapters;


use App\EligibilityBatch;
use App\EligibilityJob;
use App\Services\Eligibility\Entities\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Seld\JsonLint\JsonParser;

class JsonMedicalRecordAdapter
{
    /**
     * A json string that is the source data
     *
     * @var string
     */
    private $source;

    /**
     * @var MedicalRecord|null
     */
    private $medicalRecord;

    /**
     * @var Collection
     */
    private $validatedData;

    /**
     * @var bool
     */
    private $isValid = null;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    /**
     *
     *
     * @return MedicalRecord|null
     */
    public function createMedicalRecord(): ?MedicalRecord
    {

    }

    /**
     * @return mixed
     */
    public function getMedicalRecord()
    {
        return $this->medicalRecord;
    }

    /**
     * @param EligibilityBatch $eligibilityBatch
     *
     * @return EligibilityJob|null
     */
    public function createEligibilityJob(EligibilityBatch $eligibilityBatch): ?EligibilityJob
    {
        //hack to fix Lakhsmi's broken json for River city list from september 2018
        if ($eligibilityBatch->practice_id == 119) {
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

        $this->isValid = $this->validate($coll);

        if ($this->isValid) {
            $this->validatedData = $coll;
        }

        return $this->isValid;
    }

    /**
     * @return Collection
     */
    private function decode()
    {
        $isJson = is_json($this->source);

        if ( ! $isJson) {
            $parser = new JsonParser;

            try {
                $parser->parse($this->source, JsonParser::DETECT_KEY_CONFLICTS);
            } catch (\Exception $e) {
                \Log::debug('NOT VALID JSON: ' . json_encode($e->getDetails()) . $this->source);
            }

            return collect();
        }

        $decoded = json_decode($this->source, true);

        return collect($decoded);
    }

    private function validate(Collection $coll): bool
    {
        //@todo: implement validation rules
        return true;
    }

    private function getKey(EligibilityBatch $eligibilityBatch)
    {
        $key = $eligibilityBatch->practice->name
               . $this->validatedData->get('first_name')
               . $this->validatedData->get('last_name');

        $dob = null;

        try {
            $dob = Carbon::parse($this->validatedData->get('date_of_birth'))->toDateString();
        } catch (\Exception $e) {
            \Log::debug("Could not parse `date_of_birth`. Value {$this->validatedData->get('date_of_birth')}. Key: `$key`. {$e->getMessage()}, {$e->getCode()}. Source json string: `$this->source`");
        }

        return $key
               . $dob ?? $this->validatedData->get('date_of_birth');
    }
}
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
use Illuminate\Support\Collection;

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
    public function firstOrUpdateOrCreateEligibilityJob(EligibilityBatch $eligibilityBatch): ?EligibilityJob
    {
        if ( ! $this->isValid()) {
            return null;
        }

        $hash = $this->getKey($eligibilityBatch);

        $job = EligibilityJob::whereHash($hash)->first();

        if ( ! $job) {
            $job = EligibilityJob::create([
                'batch_id' => $eligibilityBatch->id,
                'hash'     => $hash,
                'data'     => $this->validatedData->all(),
            ]);
        } elseif ($eligibilityBatch->shouldSafeReprocess()) {
            $job = EligibilityJob::updateOrCreate([
                'batch_id' => $eligibilityBatch->id,
                'hash'     => $hash,
            ], [
                'data'     => $this->validatedData->all(),
                'messages' => null, //reset since we are re-processing
                'outcome'  => null, //reset since we are re-processing
                'status'   => 0, //reset since we are re-processing
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
        return $eligibilityBatch->practice->name
               . $this->validatedData->get('first_name')
               . $this->validatedData->get('last_name')
               . $this->validatedData->get('patient_id')
               . $this->validatedData->get('city')
               . $this->validatedData->get('state')
               . $this->validatedData->get('zip');
    }
}
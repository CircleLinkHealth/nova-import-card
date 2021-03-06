<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility;

use CircleLinkHealth\Core\Exceptions\FileNotFoundException;
use CircleLinkHealth\Eligibility\Adapters\MultipleFiledsTemplateToJson;
use CircleLinkHealth\Eligibility\DTO\CsvPatientList;
use CircleLinkHealth\Eligibility\Notifications\EligibilityBatchProcessed;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\Enrollee;

class ProcessEligibilityService
{
    use ValidatesEligibility;

    /**
     * @param $type
     * @param array $options
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function createBatch($type, int $practiceId, $options = [])
    {
        return EligibilityBatch::create(
            [
                'type'        => $type,
                'practice_id' => $practiceId,
                'status'      => EligibilityBatch::STATUSES['not_ready_to_start'],
                'options'     => $options,
            ]
        );
    }

    /**
     * @param $folder
     * @param $fileName
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     * @param bool $finishedReadingFile
     * @param null $filePath
     *
     * @return \Illuminate\Database\Eloquent\Model|ProcessEligibilityService
     */
    public function createClhMedicalRecordTemplateBatch(
        $folder,
        $fileName,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems,
        $finishedReadingFile = false,
        $filePath = null
    ) {
        return $this->createBatch(
            EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE,
            $practiceId,
            [
                'folder'              => $folder,
                'fileName'            => $fileName,
                'filePath'            => $filePath,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
                'finishedReadingFile' => (bool) $finishedReadingFile,
                //did the system read all lines from the file and create eligibility jobs?
            ]
        );
    }

    /**
     * @param $patientListCsvFilePath
     *
     * @throws \Exception
     *
     * @return array
     */
    public function createEligibilityJobFromCsvBatch(EligibilityBatch $batch, $patientListCsvFilePath)
    {
        if ( ! file_exists($patientListCsvFilePath)) {
            throw new FileNotFoundException();
        }

        return iterateCsv(
            $patientListCsvFilePath,
            function ($row) use ($batch) {
                $csvPatientList = new CsvPatientList(collect([$row]));
                $isValid = $csvPatientList->guessValidatorAndValidate();

                if ( ! $isValid) {
                    return [
                        'error' => 'This csv does not match any of the supported templates. you can see supported templates here https://drive.google.com/drive/folders/1zpiBkegqjTioZGzdoPqZQAqWvXkaKEgB',
                    ];
                }

                return $this->createEligibilityJobFromCsvRow($row, $batch);
            }
        );
    }

    /**
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityJob|\Illuminate\Database\Eloquent\Model
     */
    public function createEligibilityJobFromCsvRow(array $patient, EligibilityBatch $batch)
    {
        $patient = $this->transformCsvRow($patient);

        $validator = $this->validateRow($patient);

        $mrn = $patient['mrn'] ?? $patient['mrn_number'] ?? $patient['patient_id'];

        $hash = $batch->practice->name.$patient['first_name'].$patient['last_name'].$mrn;

        return EligibilityJob::create(
            [
                'batch_id' => $batch->id,
                'hash'     => $hash,
                'data'     => $patient,
                'errors'   => $validator->fails()
                    ? $validator->errors()
                    : null,
            ]
        );
    }

    /**
     * @param $dir
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createGoogleDriveCcdsBatch(
        $dir,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(
            EligibilityBatch::TYPE_GOOGLE_DRIVE_CCDS,
            $practiceId,
            [
                'dir'                 => $dir,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
            ]
        );
    }

    /**
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function createSingleCSVBatch(
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems
    ) {
        return $this->createBatch(
            EligibilityBatch::TYPE_ONE_CSV,
            $practiceId,
            [
                //SAVING patientList has been DEPRECATED on Jan 11 2019
                'patientList'         => [],
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
            ]
        );
    }

    /**
     * @param $folder
     * @param $fileName
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     * @param null $filePath
     *
     * @return \Illuminate\Database\Eloquent\Model|ProcessEligibilityService
     */
    public function createSingleCSVBatchFromGoogleDrive(
        $folder,
        $fileName,
        int $practiceId,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems,
        $filePath = null
    ) {
        return $this->createBatch(
            EligibilityBatch::TYPE_ONE_CSV,
            $practiceId,
            [
                'folder'   => $folder,
                'fileName' => $fileName,
                'filePath' => $filePath,
                //did the system read all lines from the file and create eligibility jobs?
                'finishedReadingFile' => false,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
            ]
        );
    }

    /**
     * Edit the details of the eligibility batch.
     *
     * @param array $options
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function editBatch(EligibilityBatch $batch, $options = [])
    {
        $updated = $batch->update(
            [
                'status'  => EligibilityBatch::STATUSES['not_started'],
                'options' => $options,
            ]
        );

        return $batch->fresh();
    }

    public function notify(EligibilityBatch $batch)
    {
        if (isProductionEnv()) {
            sendSlackMessage(
                '#parse_enroll_import',
                "Hey I just processed this list, it's crazy. Here's some patients, call them maybe? {$batch->linkToView()}"
            );
        }

        optional($batch->initiatorUser)->notify(new EligibilityBatchProcessed($batch));
    }

    /**
     * Store updated EligibilityBatch details for reprocessing. Delete existing EligibilityJobs if option `reprocess
     * from scratch` was chosen.
     *
     * @param $folder
     * @param $fileName
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     * @param $reprocessingMethod
     *
     * @return \CircleLinkHealth\SharedModels\Entities\EligibilityBatch
     */
    public function prepareClhMedicalRecordTemplateBatchForReprocessing(
        EligibilityBatch $batch,
        $folder,
        $fileName,
        $filterLastEncounter,
        $filterInsurance,
        $filterProblems,
        $reprocessingMethod
    ) {
        $batch = $this->editBatch(
            $batch,
            [
                'folder'              => $folder,
                'fileName'            => $fileName,
                'filterLastEncounter' => (bool) $filterLastEncounter,
                'filterInsurance'     => (bool) $filterInsurance,
                'filterProblems'      => (bool) $filterProblems,
                //did the system read all lines from the file and create eligibility jobs?
                //reset to false so that system will read the file again
                'finishedReadingFile' => false,
                'reprocessingMethod'  => $reprocessingMethod,
            ]
        );

        if (EligibilityBatch::REPROCESS_FROM_SCRATCH == $reprocessingMethod) {
            $deletedJobs = EligibilityJob::whereBatchId($batch->id)
                ->forceDelete();

            $deletedEnrollees = Enrollee::whereBatchId($batch->id)
                ->delete();
        }

        return $batch;
    }

    /**
     * @param $patient
     *
     * @return mixed
     */
    private function transformCsvRow($patient)
    {
        return MultipleFiledsTemplateToJson::fromRow($patient);
    }
}

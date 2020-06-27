<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class FixEnsureCCDAPatientIdMatchesPatientInfo extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set ccdas.patient_id from patient info';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:ccdas:user_id_from_patient_info {minId=1}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function compare($textone, $texttwo)
    {
        $arr1 = str_split($textone);
        $arr2 = str_split($texttwo);

        sort($arr1);
        sort($arr2);

        $text1Sorted = implode('', $arr1);
        $text2Sorted = implode('', $arr2);

        return $text1Sorted == $text2Sorted;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Ccda::withTrashed()
            ->orderBy('id')
            ->where('id', '>=', $this->argument('minId'))
            ->with('patient.patientInfo')
            ->whereHas('patient.patientInfo', function ($q) {
                $q->whereNotNull('mrn_number');
            })
            ->chunkById(200, function ($ccds) {
                foreach ($ccds as $ccd) {
                    $this->warn("Processing CCDA[$ccd->id]");

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && strtolower(trim($ccd->patient_last_name)) == strtolower(trim($ccd->patient->last_name))
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->eq(\Carbon::parse($ccd->patient_dob))
                    ) {
                        $this->line("OK CCDA[$ccd->id]");

                        continue;
                    }

                    if (is_null($ccd->practice_id)
                    && $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && strtolower(trim($ccd->patient_last_name)) == strtolower(trim($ccd->patient->last_name))
                    && $ccd->patient->patientInfo->birth_date->eq(\Carbon::parse($ccd->patient_dob))
                    ) {
                        $ccd->practice_id = $ccd->patient->program_id;
                        $ccd->save();
                        $this->line("OK CCDA[$ccd->id] was missing practice ID[$ccd->practice_id]");
                        continue;
                    }

                    if ( ! $ccd->json) {
                        $this->error("UNPARSED CCDA[$ccd->id] User_ID[$ccd->patient_id]");

                        $ccd->blueButtonJson();
                    }

                    if (strtolower(trim($ccd->patient_last_name)) == strtolower(trim($ccd->patient->last_name))
                        && $ccd->practice_id == $ccd->patient->program_id
                    && $ccd->patient->patientInfo->birth_date->eq(\Carbon::parse($ccd->patient_dob))) {
                        $this->line("OK CCDA[$ccd->id] Different MRN, same last name and dob");
                        continue;
                    }

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && $this->compare(strtolower(trim($ccd->patient_first_name.$ccd->patient_last_name)), strtolower(trim($ccd->patient->first_name.$ccd->patient->last_name)))
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->eq(\Carbon::parse($ccd->patient_dob))
                    ) {
                        $this->line("OK CCDA[$ccd->id] Different name, but with same characters");

                        continue;
                    }

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && 1 === levenshtein(extractLetters(strtolower(trim($ccd->patient_first_name.$ccd->patient_last_name))), extractLetters(strtolower(trim($ccd->patient->first_name.$ccd->patient->last_name))))
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->eq(\Carbon::parse($ccd->patient_dob))
                    ) {
                        $this->line("OK CCDA[$ccd->id] Different name, one contains middle initial");

                        continue;
                    }

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && 1 === levenshtein(extractLetters(strtolower(trim($ccd->patient_first_name.$ccd->patient_last_name))), extractLetters(strtolower(trim($ccd->patient->first_name.$ccd->patient->last_name))))
                        && $ccd->practice_id == $ccd->patient->program_id
                    ) {
                        $this->line("OK CCDA[$ccd->id] Different DOB");

                        $ccd->patient->patientInfo->birth_date = \Carbon::parse($ccd->patient_dob);
                        $ccd->patient->patientInfo->save();

                        continue;
                    }

                    $this->error("NOT OK CCDA[$ccd->id] User_ID[$ccd->patient_id]");
                    $ccd->patient_id = null;
                    $ccd->save();
                }
            });
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Core\Helpers\StringHelpers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
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
            ->where(function ($q) {
                $q
                    ->whereNull('practice_id')
                    ->orWhereHas('practice', function ($q) {
                        $q->where('is_demo', false);
                    });
            })
            ->chunkById(200, function ($ccds) {
                foreach ($ccds as $ccd) {
                    $this->warn("Processing CCDA[$ccd->id]");

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && strtolower(trim($ccd->patient_last_name)) == strtolower(trim($ccd->patient->last_name))
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->isSameDay(\Carbon::parse($ccd->patient_dob))
                    ) {
                        $this->line("OK CCDA[$ccd->id]");

                        continue;
                    }

                    if (is_null($ccd->practice_id)
                    && $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && strtolower(trim($ccd->patient_last_name)) == strtolower(trim($ccd->patient->last_name))
                    && $ccd->patient->patientInfo->birth_date->isSameDay(\Carbon::parse($ccd->patient_dob))
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

                    $safeDob = ImportPatientInfo::parseDOBDate($ccd->bluebuttonJson()->demographics->dob);
                    $data = $ccd->bluebuttonJson();
                    $data->demographics->dob = $safeDob->toDateString();
                    $ccd->json = json_encode($data);

                    if (extractLetters(strtolower(trim($ccd->patient_last_name))) == extractLetters(strtolower(trim($ccd->patient->last_name)))
                        && $ccd->practice_id == $ccd->patient->program_id
                    && $ccd->patient->patientInfo->birth_date->isSameDay($safeDob)) {
                        $this->line("OK CCDA[$ccd->id] Different MRN, same last name and dob");
                        continue;
                    }

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && StringHelpers::areSameStringsIfYouCompareOnlyLetters($ccd->patient_first_name.$ccd->patient_last_name, $ccd->patient->first_name.$ccd->patient->last_name)
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->isSameDay($safeDob)
                    ) {
                        $this->line("OK CCDA[$ccd->id] Different name, but with same characters");

                        continue;
                    }

                    if (
                        $ccd->bluebuttonJson()->demographics->mrn_number == $ccd->patient->patientInfo->mrn_number
                        && StringHelpers::areSameStringsIfYouCompareOnlyLetters($ccd->bluebuttonJson()->demographics->name->given[0] ?? ''.$ccd->bluebuttonJson()->demographics->name->family, $ccd->patient->first_name.$ccd->patient->last_name)
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->isSameDay($safeDob)
                    ) {
                        $this->line("OK CCDA[$ccd->id] Different name, but with same characters");

                        continue;
                    }

                    if (
                        $ccd->patient_mrn == $ccd->patient->patientInfo->mrn_number
                        && 1 === levenshtein(extractLetters(strtolower(trim($ccd->patient_first_name.$ccd->patient_last_name))), extractLetters(strtolower(trim($ccd->patient->first_name.$ccd->patient->last_name))))
                        && $ccd->practice_id == $ccd->patient->program_id
                        && $ccd->patient->patientInfo->birth_date->isSameDay($safeDob)
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

                        $answer = $this->choice('Should I save the CCD DOB to the PatientInfo?', ['y', 'n'], 'n');

                        if ('y' == $answer) {
                            $ccd->patient->patientInfo->birth_date = $safeDob;
                            $ccd->patient->patientInfo->save();
                        }

                        continue;
                    }

                    //NBI sends us a followup list with the valid MRN and DOB
                    //NBI DOB and MRN in CCD are not always correct
                    if (201 != $ccd->practice_id) {
                        $this->error("NOT OK CCDA[$ccd->id] User_ID[$ccd->patient_id]");

                        $u = User::ofType(['survey-only', 'participant'])
                            ->when($ccd->practice_id, function ($q) use ($ccd) {
                                $q->ofPractice($ccd->practice_id);
                            })
                            ->where('last_name', $ccd->patient_last_name)
                            ->whereHas('patientInfo', function ($q) use ($safeDob) {
                                $q->where('birth_date', $safeDob);
                            })
                            ->with('patientInfo')
                            ->first();

                        $options = ['n'];
                        $message = "
                        An automatic action could not be taken.
                        Choose an option: \n
                        
                        Type 'n' to save `ccd->patient_id = null`.
                        
                        CCD Data:
                            FName $ccd->patient_first_name
                            LName $ccd->patient_last_name
                            MRN $ccd->patient_mrn
                            Actual DOB $ccd->patient_dob
                            Safe DOB {$safeDob->toDateString()}
                            Practice $ccd->practice_id
                            PatientId $ccd->patient_id
                            CCDAId $ccd->id
                            ";

                        if ($u) {
                            array_push($options, 'r');
                            $message .= "
                        Below seems like a potential match.
                        Type 'r' save `ccd->patient_id = $u->id`.
                            
                            FName $u->first_name
                            LName $u->last_name
                            MRN {$u->patientInfo->mrn_number}
                            DOB {$u->patientInfo->birth_date->toDateString()}
                            Practice $u->program_id
                            PatientId $u->id
                       
                            ";
                        }

                        $answer = $this->choice($message, $options, 'n');

                        if ('n' === $answer) {
                            $ccd->patient_id = null;
                            $ccd->save();
                            $this->line("Saving patient_id=null CCDA[$ccd->id]");

                            continue;
                        }

                        if ('r' === $answer) {
                            $ccd->patient_id = $u->id;
                            $ccd->save();
                            $this->line("Saving patient_id=$u->id CCDA[$ccd->id]");

                            continue;
                        }

                        $this->line("Doing nothing for CCDA[$ccd->id]");
                    }
                }
            });
    }
}

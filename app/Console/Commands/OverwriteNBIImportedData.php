<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Search\ProviderByName;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

/**
 * NOTE: This command is a quickfix to save Sara time from manually looking up data from NBI sheet.
 *
 * Class OverwriteNBIImportedData
 */
class OverwriteNBIImportedData extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Overwrite data for NBI ImportedMedicalRecords';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nbi:overwrite';
    private $nbiPractice;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( ! $this->nbiPractice()) {
            $this->error('NBI practice not found');

            return;
        }

        $result = Ccda::where('practice_id', $this->nbiPractice()->id)->whereNull('billing_provider_id')->has('patient')->with('patient')->get()->map(
            function ($ccda) {
                $this->info("Checking CCDA id: $ccda->id");

                return [
                    'imr_id'       => $ccda->id,
                    'was_replaced' => $this->lookupAndReplacePatientData($ccda),
                ];
            }
        );

        $this->table(['imr_id', 'was_replaced'], $result->all());
    }

    /**
     * @return bool
     */
    public function lookupAndReplacePatientData(Ccda $ccda)
    {
        if ( ! $this->nbiPractice()) {
            return;
        }

        $datas = SupplementalPatientData::forPatient($this->nbiPractice()->id, $ccda->patient_first_name, $ccda->patient_last_name, $ccda->patientDob());

        if ($datas) {
            $map = [
                'HUSSAINI,RAFIA'     => 11493,
                'AYUB,MUHAMMED'      => 11491,
                'BUSTILLO,JOSE R'    => 11495,
                'ODERANTI,JOSHUA D'  => 11499,
                'SRIVASTAVA,SUSHAMA' => 11494,
                'PATEL,MUKESH M'     => 11498,
                'SICAT,JON'          => 11497,
                'GARCIA,JOHANNY'     => 11492,
                'ENGELL,CHRISITAN D' => 11496,
            ];

            if ($datas->provider) {
                $term                      = strtoupper($datas->provider);
                $ccda->billing_provider_id = $map[$term] ?? optional(ProviderByName::first($term))->id;
            }

            $ccda->practice_id = $this->nbiPractice()->id;
            $ccda->location_id = $this->nbiPractice()->primaryLocation()->id;
            $ccda->save();
            $ccda->patient->patientInfo->mrn_number = $datas->mrn;
            $ccda->patient->patientInfo->save();

            return true;
        }

        return false;
    }

    public function nbiPractice(): ?Practice
    {
        if ( ! $this->nbiPractice) {
            $this->nbiPractice = Practice::whereName(ReplaceFieldsFromSupplementaryData::NBI_PRACTICE_NAME)->with(
                'locations'
            )->first();
        }

        return $this->nbiPractice;
    }
}

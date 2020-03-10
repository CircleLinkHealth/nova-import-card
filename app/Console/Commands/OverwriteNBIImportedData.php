<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Search\ProviderByName;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
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
        $result = \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord::whereNull('patient_id')->whereNull('billing_provider_id')->get()->map(
            function ($imr) {
                $this->info("Checking ImportedMedicalRecord id: $imr->id");

                return [
                    'imr_id'       => $imr->id,
                    'was_replaced' => $this->lookupAndReplacePatientData($imr),
                ];
            }
        );

        $this->table(['imr_id', 'was_replaced'], $result->all());
    }

    /**
     * @return bool
     */
    public function lookupAndReplacePatientData(ImportedMedicalRecord $imr)
    {
        $mr    = $imr->medicalRecord();
        $dem   = $imr->demographics()->first();
        $datas = \CircleLinkHealth\Eligibility\Entities\PatientData::where(
            'first_name',
            'like',
            "{$dem->first_name}%"
        )->where(
            'last_name',
            $dem->last_name
        )->where('dob', $dem->dob)->first();
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
                $term                     = strtoupper($datas->provider);
                $imr->billing_provider_id = $map[$term] ?? optional(ProviderByName::first($term))->id;
            }

            $imr->practice_id = 201;
            $imr->location_id = 971;
            $imr->save();
            $dem->mrn_number = $datas->mrn;
            $dem->save();

            if ( ! empty($datas->primary_insurance)) {
                $insurance = \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog::create(
                    [
                        'medical_record_id'   => $mr->id,
                        'medical_record_type' => get_class($mr),
                        'name'                => $datas->primary_insurance,
                        'approved'            => false,
                        'import'              => true,
                    ]
                );
            }

            if ( ! empty($datas->secondary_insurance)) {
                $insurance = \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog::create(
                    [
                        'medical_record_id'   => $mr->id,
                        'medical_record_type' => get_class($mr),
                        'name'                => $datas->secondary_insurance,
                        'approved'            => false,
                        'import'              => true,
                    ]
                );
            }

            return true;
        }

        return false;
    }
}

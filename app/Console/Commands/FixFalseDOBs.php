<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Enrollee;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Importer\Models\ItemLogs\DemographicsLog;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Console\Command;

class FixFalseDOBs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix DOB imported on current date';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:dob';

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
     * @return mixed
     */
    public function handle()
    {
        Patient::where('birth_date', '>=', \Carbon\Carbon::now()->subWeek())->where('birth_date', '<=', \Carbon\Carbon::now()->addWeek())->chunkById(100, function ($patients) {
            $patients->each(function (Patient $patient) {
                $imr = \App\Models\MedicalRecords\ImportedMedicalRecord::find($patient->imported_medical_record_id);
                $mr = $imr->medicalRecord();

                $e = Enrollee::where('mrn', $mr->mrn)->firstOrFail();

                if ($e->dob->gte(Carbon::createFromDate(2000, 1, 1))) {
                    $e->dob = $e->dob->subYears(100)->toDateTimeString();
                    $e->save();
                }

                $patient->birth_date = $mr->dob = $e->dob;
                $patient->save();
                $mr->save();

                $imprtsCnt = DemographicsImport::whereMedicalRecordType(get_class($mr))->whereMedicalRecordId($mr->id)->update([
                    'dob' => $e->dob,
                ]);

                $logCnt = DemographicsLog::whereMedicalRecordType(get_class($mr))->whereMedicalRecordId($mr->id)->update([
                    'dob' => $e->dob,
                ]);
            });
        });
    }
}

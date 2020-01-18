<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog;
use App\Models\MedicalRecords\TabularMedicalRecord;
use Carbon\Carbon;
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
        TabularMedicalRecord::whereNull('dob')->chunkById(
            100,
            function ($tmr) {
                $tmr->each(
                    function (TabularMedicalRecord $mr) {
                        $e = Enrollee::where(
                            function ($q) use ($mr) {
                                $q->whereMedicalRecordType(get_class($mr))->whereMedicalRecordId($mr->id);
                            }
                        )->orWhere('mrn', $mr->mrn)->first();

                        if ( ! $e) {
                            return;
                        }
                        if ($e->dob->gte(Carbon::createFromDate(2000, 1, 1))) {
                            $e->dob = $e->dob->subYears(100)->toDateTimeString();
                            $e->save();
                        }

                        $mr->dob = $e->dob;

                        $mr->save();

                        $imprtsCnt = DemographicsImport::whereMedicalRecordType(get_class($mr))->whereMedicalRecordId(
                            $mr->id
                        )->update(
                            [
                                'dob' => $e->dob,
                            ]
                        );

                        $logCnt = DemographicsLog::whereMedicalRecordType(get_class($mr))->whereMedicalRecordId(
                            $mr->id
                        )->update(
                            [
                                'dob' => $e->dob,
                            ]
                        );
                    }
                );
            }
        );
    }
}

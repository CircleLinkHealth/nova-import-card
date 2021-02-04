<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class Fix extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subtract 100 years from DOB, if DOB is in the future for patients with DOB after given date.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dob:fix {after : Only select enrollees with DOB after this date}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $q = Enrollee::where('dob', '>', $this->argument('after'))->with('user.patientInfo');

        $this->warn("There are {$q->count()} enrollees");

        $q->eachById(function (Enrollee $enrollee) {
            $enrollee->dob = ImportPatientInfo::parseDOBDate($enrollee->dob);
            if ($enrollee->isDirty()) {
                $this->warn("Saving enrollee[{$enrollee->id}]");
                $enrollee->save();
            }

            if ($enrollee->user && $enrollee->user->patientInfo && optional($enrollee->user->patientInfo->birth_date)->isFuture()) {
                $enrollee->user->patientInfo->birth_date = ImportPatientInfo::parseDOBDate($enrollee->user->patientInfo->birth_date);
                if ($enrollee->user->patientInfo->isDirty()) {
                    $this->warn("Saving patientInfo[{$enrollee->user->patientInfo->id}]");
                    $enrollee->user->patientInfo->save();
                }
            }
        });

        return 0;
    }
}

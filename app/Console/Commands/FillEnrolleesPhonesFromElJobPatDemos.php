<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class FillEnrolleesPhonesFromElJobPatDemos extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill cell phone on Enrollees from patient_demographics.mobilephone home, and work phone from Eligibilirty Job. This probably only concerns patients pulled from Athena';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:fillphones {practiceId}';

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
        Enrollee::where('practice_id', $this->argument('practiceId'))->where(function ($q) {
            return $q->whereNull('cell_phone')->orWhere('cell_phone', '');
        })->has('eligibilityJob')->with('eligibilityJob')->chunkById(200, function ($enrollees) {
            $enrollees->each(function ($enrollee) {
                $demos = $enrollee->eligibilityJob->data['patient_demographics'] ?? [];

                $cell = null;
                $home = null;
                $work = null;

                if (array_key_exists(0, $demos)) {
                    $demos = $demos[0];
                }

                if (is_null($cell) && array_key_exists('mobilephone', $demos)) {
                    $cell = formatPhoneNumberE164($demos['mobilephone']);
                }

                if (is_null($home) && array_key_exists('homephone', $demos)) {
                    $home = formatPhoneNumberE164($demos['homephone']);
                }

                if (is_null($work) && array_key_exists('workphone', $demos)) {
                    $work = formatPhoneNumberE164($demos['workphone']);
                }

                if ($cell || $home || $work) {
                    $this->warn("Assigning cell phone to enrolleeid:{$enrollee->id}.");
                    $enrollee->cell_phone = $cell;
                    $enrollee->home_phone = $home;
                    $enrollee->other_phone = $work;

                    $data = $enrollee->eligibilityJob->data;
                    $data['cell_phone'] = $cell;
                    $data['home_phone'] = $home;
                    $data['work_phone'] = $work;
                    $enrollee->eligibilityJob->data = $data;

                    \DB::transaction(function () use ($enrollee) {
                        $enrollee->save();
                        $enrollee->eligibilityJob->save();
                    });
                }
            });
        });

        $this->comment('Finished');
    }
}

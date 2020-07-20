<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class FixDuplicateMedications extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate Medications from all patients.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:duplicate-medications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::ofType('participant')
            ->whereIn('id', function ($q) {
                $q->select('patient_id')
                    ->from('ccd_medications')
                    ->groupBy('patient_id', 'name')
                    ->havingRaw('COUNT(*) > 1');
            })->orderByDesc('id')
            ->with('ccdMedications')
            ->chunk(200, function ($users) {
                \DB::transaction(function () use ($users) {
                    foreach ($users as $user) {
                        $deleted = $user->ccdMedications()->whereNotIn('id', $user->ccdMedications->unique(function ($m) {
                            return str_replace(' ', '', strtolower($m->name));
                        })->pluck('id')->all())->delete();
                        $this->info("Deleting $deleted medications for patient_user[{$user->id}]");
                    }
                });
            });
    }
}

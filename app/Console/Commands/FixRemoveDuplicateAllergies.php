<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class FixRemoveDuplicateAllergies extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate Allergies from all patients.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:duplicate-allergies';

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
                    ->from('ccd_allergies')
                    ->groupBy('patient_id', 'allergen_name')
                    ->havingRaw('COUNT(*) > 1');
            })->orderByDesc('id')
            ->with('ccdAllergies')
            ->chunk(100, function ($users) {
                \DB::transaction(function () use ($users) {
                    foreach ($users as $user) {
                        $deleted = $user->ccdAllergies()->whereNotIn('id', $user->ccdAllergies->unique(function ($m) {
                            return str_replace(' ', '', strtolower($m->allergen_name));
                        })->pluck('id')->all())->delete();
                        $this->info("Deleting $deleted allergies for patient_user[{$user->id}]");
                    }
                });
            });
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class SendAllCarePlansToPractice extends Command
{
    use DryRunnable;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forward all Care Plans that where ever approved by a practice';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'task:forward-all-practice-careplans';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $practiceId = $this->argument('practice_id');

        $query = User::ofPractice($practiceId)->ofType('participant')->has('patientInfo')->whereHas('carePlan', function ($q) {
            $q->where('status', CarePlan::PROVIDER_APPROVED);
        })->with('carePlan');

        $countToSend = $query->count();

        $this->warn("Will forward for practice ID $practiceId");
        $this->warn("$countToSend patients to send.");

        $sent = collect();

        if ( ! $this->isDryRun()) {
            $query->chunk(10, function ($users) use (&$sent) {
                $users->each(function (User $user) use (&$sent) {
                    /** @var CarePlan $careplan */
                    $careplan = $user->carePlan;

                    $careplan->forward();

                    $sent->push([
                        'careplan_id' => $careplan->id,
                        'patient_id'  => $user->id,
                    ]);
                });
            });
        }

        $countSent = $sent->count();

        $this->warn("$countSent patients sent.");

        $this->table(['patient_id', 'careplan_id'], $sent->all());
    }

    protected function getArguments()
    {
        return  [
            ['practice_id',
                InputArgument::REQUIRED,
                'The practice id (numeric).', ],
        ];
    }
}

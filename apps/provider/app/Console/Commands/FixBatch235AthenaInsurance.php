<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FixBatch235AthenaInsurance extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:insurance';

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
        Enrollee::whereBatchId(235)->with('eligibilityJob')->chunkById(100, function (Collection $eJs) {
            $eJs->each(function (Enrollee $e) {
                $this->warn("Starting Enrollee $e->id");
                foreach ($e->eligibilityJob->data['insurances'] as $insurance) {
                    if (empty($e->primary_insurance)) {
                        $e->primary_insurance = $insurance['insuranceplanname'];
                        $e->save();
                    } elseif (empty($e->secondary_insurance)) {
                        $e->secondary_insurance = $insurance['insuranceplanname'];
                        $e->save();
                    } elseif (empty($e->tertiary_insurance)) {
                        $e->tertiary_insurance = $insurance['insuranceplanname'];
                        $e->save();
                    }
                }
                $this->line("Finishing Enrollee $e->id");
            });
        });

        $this->line('Command ran.');
    }
}

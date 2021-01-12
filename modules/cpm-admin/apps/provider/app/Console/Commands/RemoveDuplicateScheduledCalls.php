<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoveDuplicateScheduledCalls extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If patients have more than one scheduled calls, it will only keep the most recently update.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:rm-dp-sch';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $success = \DB::statement('DELETE n1 FROM calls n1, calls n2 WHERE n1.id < n2.id AND n1.inbound_cpm_id = n2.inbound_cpm_id AND n1.status = \'scheduled\' AND n1.status = n2.status AND n1.`type` = \'call\' AND n1.`type` = n2.`type` AND n1.`is_cpm_outbound` = 1 AND n1.`is_cpm_outbound` = n2.`is_cpm_outbound`');

        if (true === (bool) $success) {
            $this->line('Duplicate scheduled calls deleted');
        } else {
            $this->error('Duplicate scheduled calls were not deleted');
        }
    }
}

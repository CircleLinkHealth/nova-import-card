<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Call;
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
        $delCount = 0;

        $users = \DB::select('select inbound_cpm_id, count(*) as c
from calls
where status = \'scheduled\'
group by inbound_cpm_id
having c > 1
');

        foreach ($users as $u) {
            $calls = Call::scheduled()->where(
                'inbound_cpm_id',
                $u->inbound_cpm_id
            )->orderByDesc('updated_at')->get();

            for ($i = 1; $i < $calls->count(); ++$i) {
                $deleted = $calls[$i]->delete();

                if ($deleted) {
                    ++$delCount;
                }
            }
        }

        echo "${delCount} rows deleted.";
    }
}

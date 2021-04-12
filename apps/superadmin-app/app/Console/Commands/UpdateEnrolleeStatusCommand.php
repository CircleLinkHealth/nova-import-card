<?php

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class UpdateEnrolleeStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:enrollee-status {practiceId} {status} {enrolleeIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Enrollee Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getValidatedStatus()
    {
        if (! in_array($this->argument('status'), [
            Enrollee::QUEUE_AUTO_ENROLLMENT,
            Enrollee::TO_CALL,
            Enrollee::CONSENTED,
            Enrollee::ENROLLED,
            Enrollee::ELIGIBLE,
        ])){
            $this->error("Given Status input is not valid.");
            abort(422);
        }
    }

    public function handle()
    {
        $count = collect($this->argument('enrolleeIds'))->count();
        $status =  $this->getValidatedStatus();
        $this->info("Updating Enrollees $count");

        $updated = Enrollee::where('practice_id', $this->argument('practiceId'))
            ->when($this->argument('enrolleeIds'), function ($enrollees){
                $enrollees->whereIn('id', $this->argument('enrolleeIds'));
            })->update([
                'status' => $status
            ]);

        $this->info("Enrollees Updated: $updated");
    }
}

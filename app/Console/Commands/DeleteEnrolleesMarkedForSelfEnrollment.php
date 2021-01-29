<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class DeleteEnrolleesMarkedForSelfEnrollment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:enrollees-marked-for-selfEnrollment {practiceId} {updatedAt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete all Enrollees with their User Patient and Role. updatedAt = in format '2021-01-28 16:00:00'";

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
     * @return int
     */
    public function handle()
    {
        $updatedAtString = $this->argument('updatedAt');
        $practiceId = intval($this->argument('practiceId'));


        /** @var Carbon $updatedAt */
        $updatedAt = Carbon::parse($updatedAtString);


      User::with(['enrollee','patientInfo','roles'])
            ->whereHas('enrollee', function ($enrollee) use ($updatedAt){
                $enrollee->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT);
                $enrollee->where('updated_at','>', $updatedAt);
            })
          ->whereDoesntHave('patientInfo', function ($patient){
              $patient->where('ccm_status', Patient::ENROLLED);
          })
          ->whereDoesntHave('roles', function ($role){
              $role->where('name', 'participant');
          })
          ->whereDoesntHave('careplan')
          ->where('program_id', $practiceId)
            ->chunk(50, function($users){
                $users->each(function ($user){
                    try {
                        $user->enrollee->forceDelete();
                        $user->patientInfo->forceDelete();
                        $user->roles()->detach();
                        $user->forceDelete();
                        $this->info("User $user->id has been processed successfully.");
                    }catch (\Exception $exception){
                        $errorMessage = $exception->getMessage();
                        $this->error("[Something went wrong with user $user->id], $errorMessage");
                    }
                });
            });

        $this->info("Done");
    }
}

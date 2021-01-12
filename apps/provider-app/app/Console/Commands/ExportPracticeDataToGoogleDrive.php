<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\QueuePatientToExport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class ExportPracticeDataToGoogleDrive extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all CarePlans and Notes as PDFs tp Google Drive for all Patients of a Practice.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export_practice:google_drive
                                    {practice_id : The Practice to export}
                                    {folder_id : The Folder ID on Google Drive. All files will be exxported there.}
                           ';

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
        $folderId = $this->argument('folder_id');

        User::ofPractice($this->argument('practice_id'))
            ->ofType('participant')
            ->with(['carePlan', 'notes'])
            ->has('carePlan')
            ->chunk(100, function ($users) use ($folderId) {
                $i = 1;
                foreach ($users as $user) {
                    \Log::debug("Queuing {$user->first_name} {$user->last_name} CLH ID:{$user->id} for Export.");

                    QueuePatientToExport::dispatch($user, $folderId)
                        ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE))
                        ->delay(Carbon::now()->addSeconds($i * 3)); //temporary way to deal with

                    ++$i;
                }
            });
    }
}

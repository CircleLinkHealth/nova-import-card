<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Http\Controllers\Postmark\PostmarkInboundCallbackMatchResults;
use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use App\Services\Postmark\PostmarkCallbackMailService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UnresolvedPostmarkCallbackTest extends Command
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
    protected $signature = 'run:puta';

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
        $postmarkCallbackService = (new PostmarkCallbackMailService());
        $startDate               = now()->startOfDay()->startOfMonth();
        $endDate                 = $startDate->copy()->endOfDay()->endOfMonth();
        
        PostmarkInboundMail::whereBetween('created_at', [$startDate, $endDate])
            ->where('from', ProcessPostmarkInboundMailJob::FROM_CALLBACK_FULL_EMAIL)
            ->chunk(50, function ($records) use ($postmarkCallbackService) {
                foreach ($records as $record) {
                    $postmarkMarkService = (new PostmarkCallbackMailService());
                    $postmarkCallbackData = $postmarkMarkService->postmarkInboundData($record->id);
                    $matchedResultsFromDB = (new PostmarkInboundCallbackMatchResults(
                        $postmarkCallbackData,
                        $record->id,
                        $postmarkCallbackService)
                    )
                        ->getMatchedPatients();

                    foreach ($matchedResultsFromDB as $userPatient) {
                        //                    Is unmatched.
                        if ($userPatient instanceof Collection) {
                            $x = 1;
                        }

                        //                    If patient is Not enrolled.
                        if ( ! $postmarkCallbackService->isPatientEnrolled($userPatient)) {
                        }
                        //                    Queue auto enrolment but un assigned CA.
                        if ($postmarkCallbackService->isQueuedForEnrollmentAndUnassigned($userPatient)) {
                        }
                        //                    Patient wants to withdraw.
                        if ($postmarkCallbackService->requestsCancellation($userPatient)) {
                        }
                    }
                }
            });

        $x = 1;
    }
}

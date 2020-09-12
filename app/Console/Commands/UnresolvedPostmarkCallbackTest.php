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
        $service   = new PostmarkCallbackMailService();
        $startDate = now()->startOfDay()->startOfMonth();
        $endDate   = $startDate->copy()->endOfDay()->endOfMonth();
        $matchedResultsFromDB = [];
         PostmarkInboundMail::whereBetween('created_at', [$startDate, $endDate])
            ->where('from', ProcessPostmarkInboundMailJob::FROM_CALLBACK_FULL_EMAIL)
            ->chunk(50, function ($records) use(&$matchedResultsFromDB) {
                foreach ($records as $record) {
                    $postmarkMarkService = (new PostmarkCallbackMailService());
                    $postmarkCallbackData = $postmarkMarkService->postmarkInboundData($record->id);
                    $matchedResultsFromDB[] = (new PostmarkInboundCallbackMatchResults($postmarkCallbackData, $record->id))
                        ->getMatchedPatients();
                }
                
            });

        $x = 1;
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Entities\PostmarkInboundMailRequest;
use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use Illuminate\Console\Command;

class ProcessPostmarkInboundMailCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process inbound mail again';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:postmark-inbound-mail {recordId}';

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
        $item = PostmarkInboundMail::findOrFail($this->argument('recordId'));
        ProcessPostmarkInboundMailJob::dispatch(new PostmarkInboundMailRequest([
            'From'     => $item->from,
            'TextBody' => $item->body,
        ]), $item->id);

        return 0;
//
//        $all = PostmarkInboundMail::where('from', ProcessPostmarkInboundMailJob::FROM_CALLBACK_MAIL)->get();
//
//        foreach ($all as $item) {
//            ProcessPostmarkInboundMailJob::dispatchNow(new PostmarkInboundMailRequest(
//                [
//                    'From'     => $item->from,
//                    'TextBody' => $item->body,
//                ]
//            ), $item->id);
//        }
//
//        return 0;
    }
}

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
    protected $signature = 'process:postmark-inbound-mail {recordIds}';

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
        $recordIds = $this->argument('recordIds');
        if (is_array($recordIds)) {
            $items = PostmarkInboundMail::whereIn('id', $recordIds)->get();
            foreach ($items as $item) {
                $this->processData($item);
            }
        }

        if (is_int($recordIds)) {
            $item = PostmarkInboundMail::findOrFail($recordIds);
            $this->processData($item);
        }

        return 0;
    }

    private function processData(PostmarkInboundMail $item)
    {
        ProcessPostmarkInboundMailJob::dispatch(new PostmarkInboundMailRequest([
            'From'     => $item->from,
            'TextBody' => $item->body,
        ]), $item->id);
    }
}

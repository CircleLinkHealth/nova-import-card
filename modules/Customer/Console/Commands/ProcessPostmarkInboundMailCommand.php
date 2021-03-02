<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Console\Commands;

use CircleLinkHealth\Customer\Jobs\ProcessPostmarkInboundMailJob;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMailRequest;
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
        PostmarkInboundMail::findMany((array) $this->argument('recordIds'))
            ->each(fn ($item) => $this->processData($item));

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

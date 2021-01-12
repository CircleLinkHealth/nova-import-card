<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReprocessDirectMailMessageAttachments extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocess direct mail attachements';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dm:reprocess {dmMessageId}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(\CircleLinkHealth\Customer\Actions\ReprocessDirectMailAttachments::class)
            ->reprocess($this->argument('dmMessageId'));

        return 0;
    }
}

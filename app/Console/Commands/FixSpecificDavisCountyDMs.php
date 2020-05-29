<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\DirectMailMessage;
use App\Services\PhiMail\IncomingMessageHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixSpecificDavisCountyDMs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocesses 3 specific DMs sent by Davis County practice.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:davis-county-dms';
    /**
     * @var IncomingMessageHandler
     */
    private $imh;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IncomingMessageHandler $imh)
    {
        parent::__construct();
        $this->imh = $imh;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dms = DirectMailMessage::findMany([4059, 4051, 4124]);

        $dms->each(function (DirectMailMessage $dm) {
            $fileContents = $dm->media()->where('collection_name', "dm_{$dm->id}_attachments_unknown")->first()->getFile();

            if (is_string($fileContents) && ! Str::contains($dm->body, $fileContents)) {
                $dm->body = $dm->body.PHP_EOL.PHP_EOL.$fileContents;
                $dm->save();
            }

            $this->imh->processCcdas($dm);
        });
    }
}

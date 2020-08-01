<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\DirectMailMessage;
use App\Jobs\DecorateUPG0506CcdaWithPdfData;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class ReprocessUpg0506DmAttachments extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocess ccd or pdf attachments for a direct mail.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upg0506:reprocess-attachments {dmIds? : Comma separated direct mail IDs.}';

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
     * @return mixed
     */
    public function handle()
    {
        $dmIds = $this->argument('dmIds');

        if (empty($dmIds)) {
            $this->warn('Please input Direct Mail id or ids.');

            return;
        }

        foreach (explode(',', $dmIds) as $dmId) {
            $dm = DirectMailMessage::findOrFail($dmId);

            //get CCDAs associated with DM, if they exist and they are UPG0506 re-run the job (DecorateUPG0506CcdaWithPdfData) to look for the pdf every 1 minute
            //no need to re-import ccd at this point.
            $dm->ccdas()->get()->each(function ($ccda) {
                if (Ccda::hasUPG0506Media()->whereId($ccda->id)->exists()) {
                    DecorateUPG0506CcdaWithPdfData::dispatch($ccda);
                }
            });

            //This will trigger UPG0506DirectMailListener, which will in turn reprocess DM's pdfs. Then if the pdf is g0506 it will be picked up by job above.
            event(new DirectMailMessageReceived($dm));

            $this->info("Queued Direct Mail with ID: $dmId for reprocessing");
        }
    }
}

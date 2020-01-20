<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Contracts\DirectMail;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class SendTestDirectMail extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test Direct Mail';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emrDirect:sendTest {to=circlelinkhealth@test.directproject.net : The receiver\'s address.}';

    /**
     * @var DirectMail
     */
    private $directMail;

    /**
     * Create a new command instance.
     *
     * @param DirectMail $directMail
     */
    public function __construct(DirectMail $directMail)
    {
        parent::__construct();

        $this->directMail = $directMail;
    }

    /**
     * Execute the console command.
     *
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     *
     * @return mixed
     */
    public function handle()
    {
        $to = $this->argument('to');

        $binaryAttachmentFilePath = getSampleNotePdfPath();
        $binaryAttachmentFileName = 'Sample CCDA';
        $ccdaAttachmentPath       = getSampleCcdaPath();
        $patient                  = new User();
        $patient->first_name      = 'Foo';
        $patient->last_name       = 'Bar';

        $sent = $this->directMail->send(
            $to,
            $binaryAttachmentFilePath,
            $binaryAttachmentFileName,
            $ccdaAttachmentPath,
            $patient
        );

        $this->output->text(var_dump($sent));
    }
}

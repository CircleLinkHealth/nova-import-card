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
     * CLHs sandbox DM address. Safe to use for testing. Not safe for PHI.
     */
    const CLH_SANDBOX_DM_EMAIL = 'circlelinkhealth@test.directproject.net';
    /**
     * The body of the test Direct Mail Message.
     */
    const TEST_DM_BODY = 'Hello there! This is a test message from CircleLink Health.';
    /**
     * The number of attachments in the test Direct Mail Message.
     */
    const TEST_DM_NUM_ATTACHMENTS = 2;
    /**
     * The subject of the test Direct Mail Message.
     */
    const TEST_DM_SUBJECT = 'Test Message from CircleLink Health';

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
        $this->output->text(var_dump($this->sendTestDM($this->argument('to'))));
    }

    public function sendTestDM(string $to)
    {
        $binaryAttachmentFilePath = getSampleNotePdfPath();
        $binaryAttachmentFileName = 'Sample CCDA';
        $ccdaAttachmentPath       = getSampleCcdaPath();
        $patient                  = new User();
        $patient->first_name      = 'Foo';
        $patient->last_name       = 'Bar';

        return $this->directMail->send(
            $to,
            $binaryAttachmentFilePath,
            $binaryAttachmentFileName,
            $ccdaAttachmentPath,
            $patient,
            self::TEST_DM_BODY,
            self::TEST_DM_SUBJECT
        );
    }
}

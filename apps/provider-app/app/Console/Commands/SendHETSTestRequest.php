<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendHETSTestRequest extends Command
{
    const ENDPOINT     = 'https://mime.hets-270-271.cms.gov/eligibility/realtime/mime';
    const HOST         = ' mime.hets-270-271.cms.gov';
    const SENDER_ID    = 'W236F83300';
    const SUBMITTER_ID = 'W236F833';

    /**
     * Send a test request to HETS.
     *
     * @var string
     */
    protected $description = 'Send a test request to HETS';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hets:test';

    /**
     * Create a new command instance.
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
        $c = new \GuzzleHttp\Client();

        $resp = $c->post(self::ENDPOINT, [
            'multipart' => [
                [
                    'name'     => 'PayloadType',
                    'contents' => 'X12_270_Request_005010X279A1',
                ],
                [
                    'name'     => 'ProcessingMode',
                    'contents' => 'RealTime',
                ],
                [
                    // Refer to Section 4.4.2 of the Phase II CORE 270: Connectivity Rule for structural guidelines for CORE envelope metadata
                    'name'     => 'PayloadID',
                    'contents' => Str::uuid()->toString(),
                ],
                [
                    //2016-02-25T19:50:40.611Z
                    'name'     => 'TimeStamp',
                    'contents' => date('Y-m-d\TH:i:s.Z'),
                ],
                [
                    'name'     => 'SenderID',
                    'contents' => SendHETSTestRequest::SENDER_ID,
                ],
                [
                    'name'     => 'ReceiverID',
                    'contents' => 'CMS',
                ],
                [
                    'name'     => 'CORERuleVersion',
                    'contents' => '2.2.0',
                ],
                [
                    //X12 Request
                    'name'     => 'Payload',
                    'contents' => '',
                ],
            ],
            'cert'    => '/cryptdata/var/deploy/certificates/hets_careplanmanager_com.pem',
            'verify'  => false, //this works, but it defeats the whole point of using SSL as it leaves us vulnerable to MITM attacks
            'ssl_key' => '/cryptdata/var/deploy/certificates/hets_careplanmanager_com.key',
        ]);
        $responseBody = (string) $resp->getBody();
        echo $resp->getStatusCode().PHP_EOL;
        dd($responseBody);
    }
}

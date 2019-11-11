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
                    'contents' => '□0000000544□
ISA*00* *00* *ZZ*SUBMITTERID *ZZ*CMS *160127*0734*^*00501*000005014*1*P*|~
GS*HS*SUBMITTERID*CMS*20160127*073411*5014*X*005010X279A1~
ST*270*000000001*005010X279A1~
BHT*0022*13*TRANSA*20160127*073411~
HL*1**20*1~
NM1*PR*2*CMS*****PI*CMS~
HL*2*1*21*1~
NM1*1P*2*IRNAME*****XX*1234567893~
HL*3*2*22*0~
TRN*1*TRACKNUM*ABCDEFGHIJ~
NM1*IL*1*LNAME*FNAME****MI*123456789A~
DMG*D8*19400401~
DTP*291*RD8*20160101-20160327~
EQ*10^14^30^42^45^48^67^A7^AD^AE^AG^BF^BG~
EQ**HC|80061~
EQ**HC|G0117~
SE*15*000000001~
GE*1*5014~
IEA*1*000005014~',
                ],
            ],
            'cert'    => '/cryptdata/var/deploy/certificates/hets_careplanmanager_com.pem',
            'verify'  => false,
            'ssl_key' => '/cryptdata/var/deploy/certificates/hets_careplanmanager_com.key',
        ]);
        $responseBody = (string) $resp->getBody();
        echo $resp->getStatusCode().PHP_EOL;
        dd($responseBody);
    }
}

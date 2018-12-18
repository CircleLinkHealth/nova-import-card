<?php

namespace App\Console\Commands;

use App\Contracts\Services\TwilioClientable;
use App\SaasAccount;
use Illuminate\Console\Command;
use Twilio\Rest\Api\V2010\Account\RecordingInstance;

class DownloadTwilioRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:download-recordings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download recordings from Twilio and store them on CPM Storage. It then deletes them from Twilio.';

    /**
     * Create a new command instance.
     *
     * @param TwilioClientable $twilioClientService
     */
    public function __construct(TwilioClientable $twilioClientService)
    {
        parent::__construct();
        $this->twilioService = $twilioClientService;
    }

    /**
     * @var TwilioClientable
     */
    public $twilioService;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client     = $this->twilioService->getClient();
        $recordings = $client->recordings->read();

        foreach ($recordings as $record) {
            $this->downloadAndDelete($record);
        }
    }

    private function downloadAndDelete(RecordingInstance $recording)
    {
        $success = $this->download($recording);
        if ($success) {
            $this->delete($recording->sid);
        }
    }

    private function download(RecordingInstance $recording): bool
    {
        $ext    = '.mp3';
        $mp3Url = str_replace_last('.json', $ext, $recording->uri);
        $url    = 'https://api.twilio.com' . $mp3Url;
        $result = $this->twilioService->downloadMedia($url);
        if ($result && $result['errorDetail']) {
            $this->sendFailedDownloadToSlack($recording->sid, $result['errorCode'], $result['errorDetail']);

            return false;
        }
        $pathOnDisk = $result['mediaUrl'];

        $uploadResult = SaasAccount::whereSlug('circlelink-health')
                                   ->first()
                                   ->addMedia($pathOnDisk)
                                   ->toMediaCollection('twilio-recordings');

        return $uploadResult->wasRecentlyCreated;
    }

    private function delete($recordingSid)
    {
        $client = $this->twilioService->getClient();
        $client->recordings($recordingSid)->delete();
    }

    private function sendFailedDownloadToSlack($recordingSid, $errorCode, $errorDetail)
    {
        sendSlackMessage(
            '#twilio-messages',
            "Failed to download recording [$recordingSid]: $errorDetail [$errorCode]"
        );
    }
}

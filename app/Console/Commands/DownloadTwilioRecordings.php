<?php

namespace App\Console\Commands;

use App\Contracts\Services\TwilioClientable;
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
        $client = $this->twilioService->getClient();
        $recordings = $client->recordings->read();

        foreach ($recordings as $record) {
            $this->downloadAndDelete($record);
        }
    }

    private function downloadAndDelete(RecordingInstance $recording)
    {
        $success = $this->download($recording);
        if ($success) {
            //$this->delete($recording->sid);
        }
    }

    private function download(RecordingInstance $recording): bool
    {
        $mp3Url = str_replace_last('.json', '.mp3', $recording->uri);
        $url = 'https://api.twilio.com' . $mp3Url;
        $result = $this->twilioService->downloadMedia($url);
        if ($result && $result['errorDetail']) {
            $this->sendFailedDownloadToSlack($recording->sid, $result['errorCode'], $result['errorDetail']);
            return false;
        }
        //todo upload to s3
        return true;
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

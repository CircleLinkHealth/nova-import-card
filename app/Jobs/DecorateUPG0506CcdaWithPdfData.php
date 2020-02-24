<?php

namespace App\Jobs;

use App\DirectMailMessage;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DecorateUPG0506CcdaWithPdfData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Ccda
     */
    protected $ccda;

    public $tries = 24;

    /**
     * Create a new job instance.
     *
     * @param Ccda $ccda
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = $ccda;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->ccda->hasUPG0506Media()){
            return;
        }

        if ($this->attempts() > 24){
            AppConfig::create([
                'config_key' => 'decorator_job_tries_inside_if',
                'config_value' => $this->attempts() ?: 'none'
            ]);
//            sendSlackMessage('#general', 'something');
            return;
        }

        AppConfig::create([
            'config_key' => 'decorator_job_tries',
            'config_value' => $this->attempts() ?: 'none'
        ]);

        if ( ! $this->ccda->hasUPG0506PdfCareplanMedia()->exists()) {

            $this->release(2);
            return;
        }

        $this->markUPG0506FlowAsDone();
    }

    private function markUPG0506FlowAsDone()
    {
        $ccdMedia = Media::where('custom_properties->is_ccda', 'true')
                         ->where('custom_properties->is_upg0506', 'true')
                         ->where('model_id', $this->ccda->id)
                         ->where('model_type', Ccda::class)->first();

        //need to find this using mrn
        $pdfMedia = Media::where('custom_properties->is_pdf', 'true')
                         ->where('custom_properties->is_upg0506',
                             'true')
                         ->where('custom_properties->care_plan->demographics->mrn_number', (string)$this->ccda->mrn)
                         ->where('model_type', DirectMailMessage::class)->first();

        $ccdData                        = $ccdMedia->custom_properties;
        $pdfData                        = $pdfMedia->custom_properties;
        $ccdData['is_upg0506_complete'] = $pdfData['is_upg0506_complete'] = 'true';

        $ccdMedia->custom_properties = $ccdData;
        $ccdMedia->save();

        $pdfMedia->custom_properties = $pdfData;
        $pdfMedia->save();
    }
}

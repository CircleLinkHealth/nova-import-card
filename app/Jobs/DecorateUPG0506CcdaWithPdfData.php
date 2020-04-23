<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\DirectMailMessage;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DecorateUPG0506CcdaWithPdfData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 24;
    /**
     * @var Ccda
     */
    protected $ccda;

    /**
     * Create a new job instance.
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
        if ( ! upg0506IsEnabled()) {
            return;
        }

        if ( ! Ccda::hasUPG0506Media()->whereId($this->ccda->id)->exists()) {
            return;
        }

        if ( ! $this->ccda->hasUPG0506PdfCareplanMedia()->exists()) {
            if (24 == $this->attempts()) {
                $messageLink = route('direct-mail.show', [$this->ccda->direct_mail_message_id]);

                //notify channel that we have not received a pdf for this ccd
                sendSlackMessage('#ccd-file-status', "Something went wrong with UPG G0506 flow. 
                \n We have not received a PDF Care Plan via EMR Direct for CCD with id: {$this->ccda->id}. 
                \n Click here to see the message where the CCD was included {$messageLink}.");

                return;
            }

            $this->release(60);

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

        $pdfMedia = Media::where('custom_properties->is_pdf', 'true')
            ->where(
                'custom_properties->is_upg0506',
                'true'
            )
            ->where('custom_properties->care_plan->demographics->mrn_number', (string) $this->ccda->mrn)
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

<?php

namespace App\Jobs;

use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DecorateUPG0506CcdaWithPdfData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Ccda
     */
    protected $ccda;
    
    /**
     * Create a new job instance.
     *
     * @param Ccda $ccda
     */
    public function __construct(Ccda $ccda)
    {
        //
        $this->ccda = $ccda;
    }
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! $this->ccda->hasUPG0506PdfCareplanMedia()->exists()) {
            return $this->release(60);
        }
        
        $this->addProblemsInstructionsFromPdf();
        
        $this->markUPG0506FlowAsDone();
    }
    
    private function addProblemsInstructionsFromPdf()
    {
        //@constantinos
        //@todo fill in this method
    }
    
    private function markUPG0506FlowAsDone()
    {
        $ccdMedia = Media::where('custom_properties->is_ccda', 'true')->where('custom_properties->is_upg0506', 'true')->where(
            'model_id',
            $this->ccda->id
        )->where('model_type', Ccda::class)->first();
    
        $pdfMedia = Media::where('custom_properties->is_ccda', 'true')->where('custom_properties->is_upg0506', 'true')->where(
            'model_id',
            $this->ccda->id
        )->where('model_type', Ccda::class)->first();
        
        $ccdData = $ccdMedia->custom_properties;
        $pdfData = $pdfMedia->custom_properties;
        $ccdData['is_upg0506_complete'] = $pdfData['is_upg0506_complete']= 'true';
        
        $ccdMedia->custom_properties = $ccdData;
        $ccdMedia->save();
    
        $pdfMedia->custom_properties = $ccdData;
        $pdfMedia->save();
    }
}

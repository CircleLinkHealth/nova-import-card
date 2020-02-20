<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;


use App\Events\PatientUserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AttachUPG0506CarePlanToPatientUser implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle a job failure.
     *
     * @param PatientUserCreated $event
     * @param \Exception $exception
     *
     * @return void
     */
    public function failed(PatientUserCreated $event, $exception)
    {
        $user = $event->getUser();
        
        \Log::channel('logdna')->info(
            'Failed to attach G0506 Care Plan to patient user.',
            [
                'patient_id'        => $user->id,
                'exception_message' => $exception->getMessage(),
            ]
        );
    }
    
    /**
     * Handle the event.
     *
     * @param PatientUserCreated $event
     *
     * @return void
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     */
    public function handle(PatientUserCreated $event)
    {
        $user = $event->getUser();
        
        $ccda = $user->ccdas()->hasUPG0506PdfCareplanMedia()->first();
        
        if ($ccda) {
            $pdfMedia = $ccda->getUPG0506PdfCareplanMedia();
            
            $filePath = storage_path($pdfMedia->file_name);
            file_put_contents($filePath, $pdfMedia->getFile());
            
            $user->addMedia($filePath)
                 ->withCustomProperties(['doc_type' => 'G0506 - PDF Care Plan'])
                 ->toMediaCollection('patient-care-documents');
        }
    }
}
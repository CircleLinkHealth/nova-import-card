<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\CarePlan;
use App\Notifications\SendAllApprovedCarePlansToPractice;
use App\Services\PdfService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class FaxApprovedCarePlans extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $pdfService = app(PdfService::class);

        $pageFileNames = [];

        foreach ($models as $model) {
            $patients = $model->patients()
                ->whereHas('patientInfo', function ($info) {
                                  $info->enrolled();
                              })
                ->whereHas('carePlan', function ($cp) {
                                  $cp->where('status', CarePlan::PROVIDER_APPROVED);
                              })
                ->take(5)
                ->get();

            foreach ($patients as $patient) {
                $pageFileNames[] = $patient->carePlan->toPdf();
            }

            $mergedFileNameWithPath = $pdfService->mergeFiles($pageFileNames);

            //testing
            $kakou = User::findOrFail(8935);
            $kakou->notify(new SendAllApprovedCarePlansToPractice($mergedFileNameWithPath));
        }
    }
}

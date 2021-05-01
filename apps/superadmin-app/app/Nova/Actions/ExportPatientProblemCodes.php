<?php

namespace App\Nova\Actions;

use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use URL;


class ExportPatientProblemCodes extends Action
{
    use InteractsWithQueue;
    use Queueable;

    protected $headings = [
        'Practice',
        'Patient ID',
        'Patient Name',
        'Problem Codes',
    ];

    protected $filename;


    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    public function getFileName(): ?string
    {
        if ( ! $this->filename) {
            $this->filename = 'patient-problem-codes' . now()->timestamp . '.csv';
        }

        return $this->filename;
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $resources = $this->resourcesToArray($models);

        $response = (new FromArray($this->getFileName(), $resources,
            array_keys($resources[0] ?? [])))->download($this->getFileName());

        if ( ! $response instanceof BinaryFileResponse || $response->isInvalid()) {
            return Action::danger(__('Resource could not be exported.'));
        }

        return Action::download(
            $this->getDownloadUrl($response),
            $this->getFileName()
        );
    }

    protected function getDownloadUrl(BinaryFileResponse $response): string
    {
        return URL::temporarySignedRoute('laravel-nova-excel.download', now()->addMinutes(5), [
            'path'     => encrypt($response->getFile()->getPathname()),
            'filename' => $this->getFileName(),
        ]);
    }

    private function resourcesToArray(Collection $models)
    {
        $array = [$this->headings];
        foreach ($models as $practice) {
            User::select(['id', 'display_name'])
                ->ofPractice($practice->id)
                ->ofType('participant')
                ->whereHas('patientInfo', fn($q) => $q->enrolled())
                ->with([
                    'ccdProblems' => fn($q) => $q->forBilling(),
                ])
                ->each(function (User $user) use (&$array, $practice) {
                    $array[] = [
                        $practice->display_name,
                        $user->id,
                        $user->display_name,
                        $this->formatProblemCodesForReport($user->ccdProblems)
                    ];
                });
        }

        return $array;
    }

    private function formatProblemCodesForReport(Collection $problems)
    {
        return $problems->isNotEmpty()
            ?
            $problems->map(
                function (Problem $problem) {
                    return $problem->icd10Code();
                }
            )->filter()
                     ->unique()
                     ->implode(', ')
            : 'N/A';
    }
}

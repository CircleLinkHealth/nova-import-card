<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Core\Exports\FromArray;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCsv extends Action
{
    use InteractsWithQueue;
    use Queueable;

    protected $filename;
    /**
     * @var bool
     */
    protected $keepColumnsOrder;

    protected $onlyColumns = [];

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
            $this->filename = 'nova-export-'.now()->timestamp.'.csv';
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

        $response = (new FromArray($this->getFileName(), $resources, array_keys($resources[0] ?? [])))->download($this->getFileName());

        if ( ! $response instanceof BinaryFileResponse || $response->isInvalid()) {
            return Action::danger(__('Resource could not be exported.'));
        }

        return Action::download(
            $this->getDownloadUrl($response),
            $this->getFileName()
        );
    }

    /**
     * @param  mixed       $filename
     * @return DownloadCsv
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;

        return $this;
    }

    public function setOnlyColumns(array $onlyColumns, $keepColumnsOrder): DownloadCsv
    {
        $this->onlyColumns      = $onlyColumns;
        $this->keepColumnsOrder = $keepColumnsOrder;

        return $this;
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
        if (empty($this->onlyColumns)) {
            return $models->all();
        }

        return $models->map(function ($m) {
            if ($this->keepColumnsOrder) {
                return collect($m->toArray())->only($this->onlyColumns)->all();
            }

            return collect($m->toArray())->only($this->onlyColumns)->sortKeys()->all();
        })->all();
    }
}

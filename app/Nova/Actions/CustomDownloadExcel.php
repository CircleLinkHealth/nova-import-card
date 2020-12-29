<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\ActionRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\LaravelNovaExcel\Actions\ExportToExcel;
use Spatie\MediaLibrary\Helpers\RemoteFile;

class CustomDownloadExcel extends ExportToExcel implements WithHeadings
{
    use InteractsWithQueue;

    public function fields()
    {
        return [];
    }

    public function handle(ActionRequest $request, Action $exportable): array
    {
        $resource = $request->resource();

        $filename = $resource::uriKey().'-'.now()->timestamp.'.xlsx';

        $wasStored = Excel::store(
            $exportable,
            $filename,
            $this->getDisk(),
            $this->getWriterType()
        );

        if ( ! $wasStored) {
            return \is_callable($this->onFailure)
                ? ($this->onFailure)($request, $filename, $resource)
                : Action::danger(__('Resource could not be exported.'));
        }

        $media = auth()->user()->addMedia(new RemoteFile($filename, $this->getDisk()))->toMediaCollection(
            'Nova Excel Export - '.$resource::uriKey()
        );

        return \is_callable($this->onSuccess)
            ? ($this->onSuccess)($request, $filename, $resource)
            : Action::download(
                $media->getUrl(),
                $filename
            );
    }
}

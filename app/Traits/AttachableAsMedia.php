<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Exceptions\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;

trait AttachableAsMedia
{
    /**
     * @param Model $model
     * @param $filePath
     * @param $mediaCollection
     *
     * @throws FileNotFoundException
     * @throws \Exception
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function attachMediaTo(Model $model, $filePath, $mediaCollection)
    {
        if ( ! file_exists($filePath)) {
            throw new FileNotFoundException("`$filePath` could not be found.");
        }

        if ( ! $model instanceof HasMedia) {
            throw new \Exception(get_class($model).' needs to implement interface '.HasMedia::class);
        }

        return $model
            ->addMedia($filePath)
            ->toMediaCollection($mediaCollection);
    }
}

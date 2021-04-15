<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

trait AttachableAsMedia
{
    /**
     * @param $filePath
     * @param $mediaCollection
     *
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     * @throws \Exception
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function attachMediaTo(Model $model, $filePath, $mediaCollection)
    {
        if ( ! $model instanceof HasMedia) {
            throw new \Exception(get_class($model).' needs to implement interface '.HasMedia::class);
        }

        return $model
            ->addMedia($filePath)
            ->toMediaCollection($mediaCollection);
    }
}

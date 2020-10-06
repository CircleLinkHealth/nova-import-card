<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming\Handlers;

use App\Exports\PracticeReports\Mediable;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use Illuminate\Support\Str;

class Zip extends BaseHandler implements Mediable
{
    const MEDIA_COLLECTION_NAME_STUB = 'dm_:dm_id:_attachments_zip';

    /**
     * Get the filename.
     */
    public function filename(): string
    {
        return 'dm_id_'.$this->dm->id.'_attachment_'.sha1($this->attachmentData).'.zip';
    }

    /**
     * Get the fullpath.
     */
    public function fullPath(): string
    {
        return storage_path($this->filename());
    }

    public function handle()
    {
        file_put_contents($path = $this->fullPath(), $this->attachmentData);

        if ( ! \Macellan\Zip\Zip::check($path)) {
            throw new InvalidArgumentException("$path is not a valid zip file");
        }

        if ($this->dm->media->where('file_name', $this->filename())->isEmpty()) {
            $this->storeAsMedia();
        }

        $zip = \Macellan\Zip\Zip::open($path);

        collect($zip->listFiles())
            ->each(function ($fileName) use ($zip) {
                if ( ! Str::endsWith(strtolower($fileName), '.xml')) {
                    return null;
                }

                $zip->extract(storage_path(), $fileName);

                (new XML($this->dm, file_get_contents(storage_path($fileName))))->handle();
            });
    }

    public function mediaCollectionName(): string
    {
        return self::mediaCollectionNameFactory($this->dm->id);
    }

    public static function mediaCollectionNameFactory(int $id)
    {
        return str_replace(':dm_id:', $id, self::MEDIA_COLLECTION_NAME_STUB);
    }

    private function storeAsMedia()
    {
        $path = $this->fullPath();

        file_put_contents($path, $this->attachmentData);

        $this->dm->addMedia($path)
            ->preservingOriginal()
            ->toMediaCollection($this->mediaCollectionName());
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 9/11/18
 * Time: 4:14 PM
 */

namespace App\Services;


use Storage;

class GoogleDrive
{
    /**
     * @param $fileName
     * @param $folder
     *
     * @return false|resource
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getFileStream($fileName, $folder)
    {
        $recursive = false;
        $contents  = collect($this->getDisk()->listContents($folder, $recursive));

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($fileName, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($fileName, PATHINFO_EXTENSION))
            ->first();

        if ( ! $file) {
            return false;
        }

        $readStream = $this->getDisk()
                           ->getDriver()
                           ->readStream($file['path']);

        return $readStream;
    }

    public function getDisk()
    {
        return Storage::disk('google');
    }
}
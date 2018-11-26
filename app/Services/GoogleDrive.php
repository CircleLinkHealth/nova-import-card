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
     * Get file read stream handle
     *
     * @param $fileName | The filename with extention as it appears on Google Drive. eg. `CLH.json`
     * @param $folder | The Google Drive folder ID. Get it from the url eg https://drive.google.com/drive/folders/{folder id}
     *
     * @return false|resource
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function getFileStream($fileName, $folder)
    {
        $contents = $this->getContents($folder);

        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($fileName, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($fileName, PATHINFO_EXTENSION))
            ->first();

        if ( ! $file) {
            return false;
        }

        $readStream = $this->getFilesystemHandle()
                           ->getDriver()
                           ->readStream($file['path']);

        return $readStream;
    }

    /**
     * Get the storafe disk handle for Google Drive
     *
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    public function getFilesystemHandle()
    {
        return Storage::disk('google');
    }

    /**
     * Get the contents of a folder in Google Drive
     *
     * @param $folder
     * @param bool $recursive
     *
     * @return \Illuminate\Support\Collection
     */
    public function getContents($folder, $recursive = false)
    {
        return collect($this->getFilesystemHandle()->listContents($folder, $recursive));
    }

    /**
     * @param $parentDir
     * @param $dirName
     * @param bool $recursive
     *
     * @return mixed
     */
    public function getDirectory($parentDir, $dirName, $recursive = false)
    {
        return $this->getContents($parentDir, $recursive)
                    ->where('type', '=', 'dir')
                    ->where('filename', '=', $dirName)
                    ->first();
    }
}
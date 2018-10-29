<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/14/18
 * Time: 7:47 PM
 */

namespace App;


use Storage;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;

class Media extends \Spatie\MediaLibrary\Models\Media
{
    /**
     * Get the file
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getFile()
    {
        return Storage::disk($this->disk)->get($this->getPath());
    }

    /**
     * Get the file
     *
     * @return string
     */
    public function downloadFile()
    {
        return Storage::disk($this->disk)->download("{$this->id}/{$this->file_name}");
    }

    /**
     * Returns the file extension.
     *
     * It is extracted from the original file name, so it should not be considered as a safe value.
     *
     * @return string The extension
     */
    public function getFileExtension()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Returns the extension based on the mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     *
     * @see guessExtension()
     * @see getClientMimeType()
     */
    public function guessFileExtension()
    {
        $type    = $this->mime_type;
        $guesser = ExtensionGuesser::getInstance();

        return $guesser->guess($type);
    }
}
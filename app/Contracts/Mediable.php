<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 2/17/20
 * Time: 12:58 AM
 */

namespace App\Exports\PracticeReports;


interface Mediable
{
    /**
     * Get the filename.
     *
     * @return string
     */
    public function filename(): string;
    
    /**
     * Get the fullpath.
     *
     * @return string
     */
    public function fullPath(): string;
    
    /**
     * The name of the Media Collection
     * @return string
     */
    public function mediaCollectionName(): string;
}
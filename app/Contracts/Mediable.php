<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

interface Mediable
{
    /**
     * Get the filename.
     */
    public function filename(): string;

    /**
     * Get the fullpath.
     */
    public function fullPath(): string;

    /**
     * The name of the Media Collection.
     */
    public function mediaCollectionName(): string;
}

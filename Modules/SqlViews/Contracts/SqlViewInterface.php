<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews\Contracts;

interface SqlViewInterface
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool;

    /**
     * Drop the sql view.
     */
    public function dropSqlView(): bool;

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string;

    /**
     * Drop and create the sql view.
     */
    public static function run(): bool;
}

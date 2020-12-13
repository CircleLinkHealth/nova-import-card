<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews;

use CircleLinkHealth\SqlViews\Contracts\SqlViewInterface;

abstract class BaseSqlView implements SqlViewInterface
{
    private const PACKAGE_NAME = 'circlelinkhealth/sqlviews-module';

    private ?VersionComparer $versionComparer;

    /**
     * BaseSqlView constructor.
     */
    public function __construct()
    {
        $this->versionComparer = new VersionComparer(self::PACKAGE_NAME, $this->getViewName());
    }

    /**
     * Create the sql view.
     */
    abstract public function createSqlView(): bool;

    /**
     * Drop the sql view.
     */
    public function dropSqlView(): bool
    {
        return \DB::statement("DROP VIEW IF EXISTS {$this->getViewName()}");
    }

    /**
     * Get the name of the sql view.
     */
    abstract public function getViewName(): string;

    /**
     * Drop and create Sql Views.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public static function run(): bool
    {
        $obj = new static();

        if ( ! $obj->shouldIgnoreVersionCheck() && ! $obj->shouldRunInternal()) {
            return false;
        }

        if ( ! $obj->shouldRun()) {
            return false;
        }

        $dropped = $obj->dropSqlView();
        if ( ! $dropped) {
            throw new \Exception("Could not drop mysql view `{$obj->getViewName()}`");
        }
        $created = $obj->createSqlView();
        if ( ! $created) {
            throw new \Exception("Could not create mysql view `{$obj->getViewName()}`");
        }

        $obj->updateDb();

        return (bool) $created && (bool) $dropped;
    }

    public function shouldIgnoreVersionCheck(): bool
    {
        return false;
    }

    public function shouldRun(): bool
    {
        return true;
    }

    public function updateDb()
    {
        $this->versionComparer->storeComposerVersionInDb();
    }

    private function shouldRunInternal(): bool
    {
        return $this->versionComparer->shouldUpdateBasedOnVersions();
    }
}

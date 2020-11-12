<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SqlViews;

use Illuminate\Support\Facades\DB;
use PHLAK\SemVer;

class VersionComparer
{
    private ?string $composerVersion = null;
    private ?string $dbVersion       = null;

    private string $packageName;
    private string $viewName;

    public function __construct(string $packageName, string $viewName)
    {
        $this->packageName = $packageName;
        $this->viewName    = $viewName;
    }

    /**
     * Logic:.
     *
     * 1. No version in db -> update
     * 2. DB version is string (branch name), composer version is sem ver -> update
     * 3. DB version is sem ver, composer version is string (branch name) -> update
     * 4. both string -> update
     * 5. both sem ver -> compare
     *
     * @return bool
     */
    public function shouldUpdateBasedOnVersions()
    {
        $dbVersionString = $this->getVersionInDb();
        if ( ! $dbVersionString) {
            return true;
        }

        $dbVersionIsSemVer       = $this->isSemVer($dbVersionString);
        $composerVersionString   = $this->getVersionInComposer();
        $composerVersionIsSemVer = $this->isSemVer($composerVersionString);

        if ($composerVersionIsSemVer && ! $dbVersionIsSemVer) {
            return true;
        }

        if ( ! $composerVersionIsSemVer && $dbVersionIsSemVer) {
            return true;
        }

        if ( ! $composerVersionIsSemVer && ! $dbVersionIsSemVer) {
            return true;
        }

        return SemVer\Version::parse($composerVersionString)->gt(SemVer\Version::parse($dbVersionString));
    }

    public function storeComposerVersionInDb(): bool
    {
        return DB::table('migrations_views')
            ->updateOrInsert([
                'name' => $this->viewName,
            ], [
                'version' => $this->getVersionInComposer(),
            ]);
    }

    private function getVersionInComposer(): ?string
    {
        if ( ! $this->composerVersion) {
            $composerStr = file_get_contents(base_path().'/composer.json');
            $jsonObj     = json_decode($composerStr, true);
            $str         = $jsonObj['require'][$this->packageName];

            $this->composerVersion = $str;
        }

        return $this->composerVersion;
    }

    private function getVersionInDb(): ?string
    {
        if ( ! $this->dbVersion) {
            $record = DB::table('migrations_views')
                ->where('name', '=', $this->viewName)
                ->first();

            $this->dbVersion = optional($record)->version;
        }

        return $this->dbVersion;
    }

    private function isSemVer(string $version): bool
    {
        try {
            SemVer\Version::parse($version);

            return true;
        } catch (SemVer\Exceptions\InvalidVersionException $e) {
            return false;
        }
    }
}

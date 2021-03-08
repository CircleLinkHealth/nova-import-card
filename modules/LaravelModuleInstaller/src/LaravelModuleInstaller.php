<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Joshbrw\LaravelModuleInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class LaravelModuleInstaller extends LibraryInstaller
{
    const DEFAULT_ROOT = 'Modules';

    /**
     * Get the fully-qualified install path
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return $this->getBaseInstallationPath().'/'.$this->getModuleName($package);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'laravel-module' === $packageType;
    }

    /**
     * Get the base path that the module should be installed into.
     * Defaults to Modules/ and can be overridden in the module's composer.json.
     * @return string
     */
    protected function getBaseInstallationPath()
    {
        if ( ! $this->composer || ! $this->composer->getPackage()) {
            return self::DEFAULT_ROOT;
        }

        $extra = $this->composer->getPackage()->getExtra();

        if ( ! $extra || empty($extra['module-dir'])) {
            return self::DEFAULT_ROOT;
        }

        return $extra['module-dir'];
    }

    /**
     * Get the module name, i.e. "joshbrw/something-module" will be transformed into "Something".
     * @throws \Exception
     * @return string
     */
    protected function getModuleName(PackageInterface $package)
    {
        $name  = $package->getPrettyName();
        $split = explode('/', $name);

        if (2 !== count($split)) {
            throw new \Exception($this->usage($name));
        }

        $splitNameToUse = explode('-', $split[1]);

        if (count($splitNameToUse) < 2) {
            throw new \Exception($this->usage($name));
        }

        if ('module' !== array_pop($splitNameToUse)) {
            throw new \Exception($this->usage($name));
        }

        return implode('', array_map('ucfirst', $splitNameToUse));
    }

    /**
     * Get the usage instructions.
     * @param  mixed  $name
     * @return string
     */
    protected function usage($name)
    {
        return "Ensure your package's name ($name) is in the format <vendor>/<name>-<module>";
    }
}

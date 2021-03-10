<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli;

use Symfony\Component\Finder\Finder;

class ApplicationFiles
{
    /**
     * Get an application Finder instance.
     *
     * @param string $path
     *
     * @return \Symfony\Component\Finder\Finder
     */
    public static function get($path)
    {
        return (new Finder())
            ->in($path)
            ->exclude('.idea')
            ->exclude('.vapor')
            ->notPath('/^'.preg_quote('tests', '/').'/')
            ->exclude('node_modules')
            ->exclude('bower_components')
            ->ignoreVcs(true)
            ->ignoreDotFiles(false);
    }
}

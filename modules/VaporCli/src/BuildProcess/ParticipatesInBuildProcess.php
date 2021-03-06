<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\BuildProcess;

use Illuminate\Filesystem\Filesystem;
use Laravel\VaporCli\Path;

trait ParticipatesInBuildProcess
{
    protected $appPath;
    protected $buildPath;
    protected $environment;
    protected $environmentType;
    protected $files;
    protected $path;
    protected $vaporPath;
    protected $vendorPath;

    /**
     * Create a new project builder.
     *
     * @param string|null $environment
     * @param mixed|null  $environmentType
     *
     * @return void
     */
    public function __construct($environment = null, $environmentType = null)
    {
        $this->environment = $environment;

        $this->appPath    = Path::app();
        $this->vendorPath = Path::vendor();
        $this->path       = Path::current();
        $this->vaporPath  = Path::vapor();
        $this->buildPath  = Path::build();

        $this->files           = new Filesystem();
        $this->environmentType = $environmentType;
    }
}

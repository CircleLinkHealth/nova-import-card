<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Joshbrw\LaravelModuleInstaller\LaravelModuleInstaller;
use PHPUnit\Framework\TestCase;

class LaravelModuleInstallerTest extends TestCase
{
    protected $composer;
    protected $config;
    protected $io;
    protected $test;

    public function setUp()
    {
        $this->io       = Mockery::mock(IOInterface::class);
        $this->composer = Mockery::mock(Composer::class);
        $this->composer->allows([
            'getPackage'         => $this->composer,
            'getDownloadManager' => $this->composer,
            'getConfig'          => $this->composer,
            'get'                => $this->composer,
        ])->shouldReceive('getExtra')->byDefault();

        $this->test = new LaravelModuleInstaller(
            $this->io,
            $this->composer
        );
    }

    /**
     * @test
     */
    public function it_can_use_compound_module_names()
    {
        $mock = $this->getMockPackage('vendor/compound-name-module');

        $this->assertEquals('Modules/CompoundName', $this->test->getInstallPath($mock));
    }

    /**
     * @test
     *
     * You can optionally include a base path name
     * in which to install.
     *
     *    "extra": {
     *      "module-dir": "Custom"
     *    },
     */
    public function it_can_use_custom_path()
    {
        $package = $this->getMockPackage('vendor/name-module');

        $this->composer->shouldReceive('getExtra')
            ->andReturn(['module-dir' => 'Custom'])
            ->getMock();

        $this->assertEquals('Custom/Name', $this->test->getInstallPath($package));
    }

    /**
     * @test
     */
    public function it_returns_modules_folder_by_default()
    {
        $mock = $this->getMockPackage('vendor/name-module');

        $this->assertEquals('Modules/Name', $this->test->getInstallPath($mock));
    }

    /**
     * @test
     *
     * Your package composer.json file must include:
     *
     *    "type": "laravel-module",
     */
    public function it_supports_laravel_module_type_only()
    {
        $this->assertFalse($this->test->supports('module'));
        $this->assertTrue($this->test->supports('laravel-module'));
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_if_given_malformed_compound_name()
    {
        $mock = $this->getMockPackage('vendor/some-compound-name');

        $this->assertEquals('Modules/Name', $this->test->getInstallPath($mock));
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_if_given_malformed_name()
    {
        $mock = $this->getMockPackage('vendor');

        $this->test->getInstallPath($mock);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_exception_if_suffix_not_included()
    {
        $mock = $this->getMockPackage('vendor/name');

        $this->test->getInstallPath($mock);
    }

    private function getMockPackage($return)
    {
        return Mockery::mock(PackageInterface::class)
            ->shouldReceive('getPrettyName')
            ->once()
            ->andReturn($return)
            ->getMock();
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\PdfService\Services\SnappyPdfWrapper;
use Composer\Autoload\ClassLoader;

require __DIR__.'/vendor/autoload.php';

class Preloader
{
    const COMPOSER_CLASSMAP_PATH = __DIR__.'/vendor/composer/autoload_classmap.php';

    private static int $count = 0;

    private array $fileMap;
    private array $ignores            = [];
    private array  $ignoresIfContains = [];

    private array $paths;

    public function __construct(string ...$paths)
    {
        $this->paths = $paths;

        $this->fileMap = array_flip(require self::COMPOSER_CLASSMAP_PATH);
    }

    public function ignore(string ...$names): Preloader
    {
        $this->ignores = array_merge(
            $this->ignores,
            $names
        );

        return $this;
    }

    public function ingoreIfContains(string ...$names): Preloader
    {
        $this->ignoresIfContains = array_merge(
            $this->ignoresIfContains,
            $names
        );

        return $this;
    }

    public function load(): void
    {
        foreach ($this->paths as $path) {
            $this->loadPath(rtrim($path, '/'));
        }

        $count = self::$count;

        echo "[Preloader] Preloaded {$count} classes".PHP_EOL;
    }

    public function paths(string ...$paths): Preloader
    {
        $this->paths = array_merge(
            $this->paths,
            $paths
        );

        return $this;
    }

    private function loadDir(string $path): void
    {
        $handle = opendir($path);

        while ($file = readdir($handle)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $this->loadPath("{$path}/{$file}");
        }

        closedir($handle);
    }

    private function loadFile(string $path): void
    {
        $class = $this->fileMap[$path] ?? null;

        if ($this->shouldIgnore($class)) {
            return;
        }

        require_once $path;

        ++self::$count;

        echo "[Preloader] Preloaded `{$class}`".PHP_EOL;
    }

    private function loadPath(string $path): void
    {
        if (is_dir($path)) {
            $this->loadDir($path);

            return;
        }

        $this->loadFile($path);
    }

    private function shouldIgnore(?string $name): bool
    {
        if (null === $name) {
            return true;
        }

        foreach ($this->ignores as $ignore) {
            if (0 === strpos($name, $ignore)) {
                return true;
            }
        }
    
        foreach ($this->ignoresIfContains as $ignore) {
            if ($this->strContains($name, $ignore)) {
                return true;
            }
        }

        return false;
    }
    
    private function strContains(string $haystack, string $needle): bool {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}

(new Preloader())
    ->paths(
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/routes',
        __DIR__.'/storage/framework/views',
        __DIR__.'/vendor/laravel/framework',
        __DIR__.'/CircleLinkHealth'
    )
    ->ignore(
        \Illuminate\Filesystem\Cache::class,
        \Illuminate\Log\LogManager::class,
        \Illuminate\Http\Testing\File::class,
        \Illuminate\Http\UploadedFile::class,
        \Illuminate\Support\Carbon::class,
        ClassLoader::class,
        Laravel\Nova\Testing\Browser\Components\IndexComponent::class,
        SnappyPdfWrapper::class,
        \PackageVersions\Installer::class,
    )->ingoreIfContains(
        'Autoload',
        'test',
        'Test',
        'tests',
        'Tests',
        'stub',
        'Stub',
        'stubs',
        'Stubs',
        'dumper',
        'Seeder',
        'Command',
        'Installer'
    )
    ->load();

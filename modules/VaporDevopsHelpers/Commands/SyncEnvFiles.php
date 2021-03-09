<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\VaporDevOpsHelpers\Commands;

use CircleLinkHealth\Core\Traits\RunsCommands;
use Dotenv\Dotenv;
use Illuminate\Console\Command;

class SyncEnvFiles extends Command
{
    use RunsCommands;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command takes in a source env file, and a blueprint env file. It will create a new file that will contain all keys from the blueprint, and fill in any values that exist in the source file.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpmvapor:syncenvfiles {source: The absolute path to the source .env file.}
                                                   {blueprint: The absolute path to the blueprint .env file.}
                                                   ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source       = $this->toCollection($this->argument('source'));
        $blueprint    = $this->toCollection($this->argument('blueprint'));
        $destFilename = $this->argument('source').now()->timestamp;

        $bar = $this->output->createProgressBar($blueprint->count());

        $blueprint->each(function ($blueprintSecret, $name) use ($source, $bar, $destFilename) {
            file_put_contents($destFilename, $this->getLine($source->get($name, ''), $name), FILE_APPEND);
            $bar->advance();
        });

        $bar->finish();
    }

    private function getLine(string $secret, string $name)
    {
        $ln = "$name=";
        if (str_contains($secret, ' ')) {
            $ln .= '"';
        }
        $ln .= "{$secret}";
        if (str_contains($secret, ' ')) {
            $ln .= '"';
        }
        $ln .= "\n";

        return $ln;
    }

    private function toCollection(string $option)
    {
        return collect((Dotenv::createImmutable(dirname($option), basename($option)))->load());
    }
}

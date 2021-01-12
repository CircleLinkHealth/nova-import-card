<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ConditionCodeLookup\Console\Commands;

use CircleLinkHealth\ConditionCodeLookup\Services\ConditionCodeLookupService;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class LookupCondition extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Look up condition codes.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lookup:condition';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->argument('type');
        $code = $this->argument('code');

        try {
            $result = self::lookup($code, $type);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->info('Result: '.json_encode($result));
    }

    public static function lookup(string $code, string $type)
    {
        if ( ! in_array($type, ['icd9', 'icd10', 'snomed', 'any'])) {
            throw new InvalidArgumentException('Invalid type. Only one of icd9, icd10, snomed or any accepted');
        }

        return app(ConditionCodeLookupService::class)->{$type}($code);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'icd9, icd10, snomed, any'],
            ['code', InputArgument::REQUIRED, 'The code to search for.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
        ];
    }
}

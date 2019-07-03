<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Traits;

use Symfony\Component\Console\Input\InputOption;

trait DryRunnable
{
    /**
     * Append this message to all console output if dry run mode is on.
     *
     * @return string
     */
    public function appendDryMessage()
    {
        return ' [nothing happened. command was issued with --dry-run]';
    }

    /**
     * Is dry run mode on?
     *
     * @return bool
     */
    public function isDryRun()
    {
        return (bool) $this->option('dry-run');
    }

    /**
     * Write a string as line output.
     *
     * @param $string
     * @param null $style
     * @param null $verbosity
     *
     * @return mixed
     */
    public function line($string, $style = null, $verbosity = null)
    {
        if ($this->isDryRun()) {
            $string = $string.$this->appendDryMessage();
        }

        return parent::line($string, $style, $verbosity);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['dry-run', 'd', InputOption::VALUE_NONE, 'Dry run. Defaults to false.', null],
        ];
    }
}

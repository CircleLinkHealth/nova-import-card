<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use Illuminate\Support\Str;
use Validator;

abstract class ReportsErrorsToSlack
{
    protected $channel         = '#background-tasks';
    protected $importingErrors = [];

    protected $rowNumber = 2;

    public function __destruct()
    {
        if ( ! empty($this->importingErrors)) {
            $rowErrors = collect($this->importingErrors)->transform(function ($item, $key) {
                return "Row: {$key} - Errors: {$item}. ";
            })->implode('\n');

            sendSlackMessage($this->reportToChannel(), "{$this->getErrorMessageIntro()} "."\n"."{$rowErrors}");
        }
    }

    /**
     * The message that is displayed before each row error is listed.
     */
    abstract protected function getErrorMessageIntro(): string;

    abstract protected function getImportingRules(): array;

    protected function reportToChannel(): string
    {
        if ( ! Str::startsWith($this->channel, '#')) {
            $this->channel = '#'.$this->channel;
        }

        return $this->channel;
    }

    protected function validateRow(array $row): bool
    {
        $validator = Validator::make(
            $row,
            $this->getImportingRules()
        );

        if ($validator->fails()) {
            $this->importingErrors[$this->rowNumber] = implode(', ', $validator->messages()->keys());

            return false;
        }

        return true;
    }
}

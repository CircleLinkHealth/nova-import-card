<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Traits;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputArgument;

trait TakesMonthAndUsersAsInputArguments
{
    /**
     * Store the Carbon instance of month here.
     *
     * @var Carbon|null
     */
    public $monthInstance;

    /**
     * The default month, if no argument is passed.
     *
     * @return Carbon
     */
    public function defaultMonth()
    {
        return Carbon::now()->subMonth()->startOfMonth();
    }

    /**
     * @return Carbon
     */
    public function month()
    {
        if ( ! $this->monthInstance) {
            $input = $this->argument('month') ?? null;

            $this->monthInstance = $input ? Carbon::createFromFormat('Y-m', $input)->startOfMonth() : $this->defaultMonth();
        }

        return $this->monthInstance;
    }

    /**
     * @return array
     */
    public function usersIds()
    {
        return (array) $this->argument('userIds') ?? [];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['month', InputArgument::OPTIONAL, 'Month to generate the invoice for in YYYY-MM format. Defaults to previous month.'],
            ['userIds', InputArgument::IS_ARRAY|InputArgument::OPTIONAL, 'Users to run the command for. Leave empty to send to all.'],
        ];
    }
}

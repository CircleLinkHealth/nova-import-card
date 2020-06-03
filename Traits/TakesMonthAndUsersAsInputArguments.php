<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Traits;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\InputArgument;

trait TakesMonthAndUsersAsInputArguments
{
    /**
     * Store the Carbon instance of month here.
     *
     * @var Carbon|null
     */
    protected $dateInstance;

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
     * Returns the month instance.
     *
     * @throws ValidationException
     *
     * @return Carbon
     */
    public function month()
    {
        if ( ! $this->dateInstance) {
            $this->initDateInstance();
        }

        return $this->dateInstance->copy();
    }

    /**
     * @return array
     */
    public function usersIds()
    {
        return (array) $this->argument('userIds') ?? [];
    }

    /**
     * @return string
     */
    protected function dateFormat()
    {
        return 'Y-m-d';
    }

    /**
     * Get a validator instance for date.
     *
     * @param $input
     *
     * @return \Illuminate\Validation\Validator
     */
    protected function dateValidator($input)
    {
        return \Validator::make(
            [
                'date' => $input,
            ],
            [
                'date' => 'required|date_format:'.$this->dateFormat(),
            ]
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'month',
                InputArgument::OPTIONAL,
                'Month to generate the invoice for in "Y-m-d": format. Defaults to previous month.',
            ],
            [
                'userIds',
                InputArgument::IS_ARRAY|InputArgument::OPTIONAL,
                'Users to run the command for. Leave empty to send to all.',
            ],
        ];
    }

    /**
     * Initialize the date instance either from input, or using default month.
     */
    protected function initDateInstance()
    {
        $input = $this->argument('month');

        if ( ! $input) {
            $this->dateInstance = $this->defaultMonth();

            return;
        }

        $validator = $this->dateValidator($input);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->messages()->first());
        }

        $this->dateInstance = Carbon::createFromFormat('Y-m-d', $input)->startOfDay();
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands;

use DateTime;
use Laravel\VaporCli\ConsoleVaporClient;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    use ProvidesSelectionMenus;

    /**
     * The input implementation.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    public $input;

    /**
     * The output implementation.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    public $output;

    /**
     * The number of rows in the last refreshed table.
     *
     * @var int
     */
    public $rowCount = 0;

    /**
     * The Vapor client instance.
     *
     * @var \Laravel\VaporCli\ConsoleVaporClient
     */
    public $vapor;

    /**
     * The DateTime representing the time the command started.
     *
     * @var \DateTime
     */
    protected $startedAt;

    /**
     * Call another console command.
     *
     * @param string $command
     *
     * @return int
     */
    public function call($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            new ArrayInput($arguments),
            Helpers::app('output')
        );
    }

    /**
     * Create a selection menu with the given choices.
     *
     * @param string $title
     * @param array  $choices
     *
     * @return mixed
     */
    public function menu($title, $choices)
    {
        return Helpers::menu($title, $choices);
    }

    /**
     * Format input into a textual table.
     *
     * @param string $style
     *
     * @return void
     */
    public function table(array $headers, array $rows, $style = 'borderless')
    {
        Helpers::table($headers, $rows, $style);
    }

    /**
     * Get an argument from the input list.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Configure the output styles for the application.
     *
     *
     * @return void
     */
    protected function configureOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle(
            'finished',
            new OutputFormatterStyle('green', 'default', ['bold'])
        );
    }

    /**
     * Ensure the user intends to manipulate the production environment.
     *
     * @param string $environment
     * @param bool   $force
     *
     * @return void
     */
    protected function confirmIfProduction($environment, $force = null)
    {
        if (($this->input->hasOption('force') &&
            $this->option('force')) ||
            'production' !== $environment) {
            return;
        }

        if ( ! Helpers::confirm('You are manipulating the production environment. Are you sure you want to proceed', false)) {
            Helpers::abort('Action cancelled.');
        }
    }

    /**
     * Execute the command.
     *
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startedAt = new DateTime();

        $this->vapor = Helpers::app(ConsoleVaporClient::class);

        Helpers::app()->instance('input', $this->input = $input);
        Helpers::app()->instance('output', $this->output = $output);

        $this->configureOutputStyles($output);

        return Helpers::app()->call([$this, 'handle']) ?: 0;
    }

    /**
     * Get the ID of an item by name.
     *
     * @param string $name
     * @param mixed  $attribute
     *
     * @return int
     */
    protected function findIdByName(array $items, $name, $attribute = 'name')
    {
        return collect($items)->first(function ($item) use ($name, $attribute) {
            return $item[$attribute] == $name;
        })['id'] ?? null;
    }

    /**
     * Get an option from the input list.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * Format input to textual table, remove the prior table.
     *
     *
     * @return void
     */
    protected function refreshTable(array $headers, array $rows)
    {
        if ($this->rowCount > 0) {
            Helpers::write(str_repeat("\x1B[1A\x1B[2K", $this->rowCount + 4));
        }

        $this->rowCount = count($rows);

        $this->table($headers, $rows);
    }
}

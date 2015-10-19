<?php namespace App\Console\Commands;

use App\CLH\Helpers\DBCleanup;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NukeItemAndMeta extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nuke:itemandmeta';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Will erase an Item in the DB (eg. \'Other Meds\'), along with all its meta.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$metaKey = $this->argument('metaKey');

        if ($this->confirm("WARNING! This will permanently delete all $metaKey items and their meta. Proceed? [yes|no]", false))
        {
            $nuke = DBCleanup::nukeItemsAndTheirMeta($metaKey);
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['metaKey', InputArgument::REQUIRED, 'The meta key of the item to be deleted.'],
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

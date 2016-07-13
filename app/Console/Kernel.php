<?php namespace App\Console;

use App\AppConfig;
use App\Console\Commands\FormatLocationPhone;
use App\Console\Commands\GeneratePatientReports;
use App\Console\Commands\Inspire;
use App\Console\Commands\MapSnomedToCpmProblems;
use App\Console\Commands\NukeItemAndMeta;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		Inspire::class,
		NukeItemAndMeta::class,
		MapSnomedToCpmProblems::class,
		FormatLocationPhone::class,
		GeneratePatientReports::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->call(function(){
			AppConfig::create([
				'config_key' => 'hello',
				'config_value' => 'test',
			]);
		})->everyMinute();
	}

}

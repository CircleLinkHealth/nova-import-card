<?php namespace App\Console;

use App\Algorithms\Calls\PredictCall;
use App\AppConfig;
use App\Console\Commands\FormatLocationPhone;
use App\Console\Commands\GeneratePatientReports;
use App\Console\Commands\Inspire;
use App\Console\Commands\MapSnomedToCpmProblems;
use App\Console\Commands\NukeItemAndMeta;

use App\Services\Calls\SchedulerService;
use App\Services\PhiMail\PhiMail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Maknz\Slack\Facades\Slack;


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
			(new PhiMail)->sendReceive();
		})->everyMinute();

        $schedule->call(function(){

			$calls = SchedulerService::getUnAttemptedCalls();
			$handled = array();

			foreach ($calls as $call){
				$handled[] = (new PredictCall(User::find($call->inbound_cpm_id), $call, false))->reconcileDroppedCallHandler();
			}

			if (! app()->environment('worker-staging')) return;

		foreach ($handled as $call){
			Slack::to('#background-tasks-dev')->send("We just fixed call: {$call->id}");
		}

            
        })->everyFiveMinutes();

	}
}

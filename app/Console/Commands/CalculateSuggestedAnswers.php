<?php

namespace App\Console\Commands;

use App\Jobs\SurveyAnswersCalculateSuggestionsJob;
use Illuminate\Console\Command;

class CalculateSuggestedAnswers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suggestAnswers {patientIds : comma separated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and suggest answers for current surveys of patients';

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
     */
    public function handle()
    {
        $patientIds = $this->argument('patientIds') ?? null;
        if ($patientIds) {
            $patientIds = explode(',', $patientIds);
        } else {
            $patientIds = [];
        }

        foreach ($patientIds as $patientId) {
            SurveyAnswersCalculateSuggestionsJob::dispatch($patientId)->onQueue('awv-high');
        }

        $this->info('Done.');
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\ReImportCcdToGetProblemTranslationCodes;
use App\User;
use Illuminate\Console\Command;

class ReImportCcdsToGetTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:ngdc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {
        User::ofType('participant')
            ->where('program_id', '=', '148')
            ->get()
            ->map(function ($patient) {
                dispatch(new ReImportCcdToGetProblemTranslationCodes($patient));
            });
    }
}

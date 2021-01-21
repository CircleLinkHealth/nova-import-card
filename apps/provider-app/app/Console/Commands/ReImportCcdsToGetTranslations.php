<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\ReImportCcdToGetProblemTranslationCodes;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class ReImportCcdsToGetTranslations extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:ngdc';

    /**
     * Create a new command instance.
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

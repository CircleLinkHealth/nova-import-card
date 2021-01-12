<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateCameronLetterCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calls GenerateCameronLetter seeder.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:cameronLetter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Artisan::call('db:seed', ['--class' => 'CircleLinkHealth\Eligibility\Database\Seeders\GenerateCameronLetter']);
    }
}

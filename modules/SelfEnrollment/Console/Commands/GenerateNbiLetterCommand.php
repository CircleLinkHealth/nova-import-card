<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateNbiLetter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateNbiLetterCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call GenerateNbiLetter Seeder.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:nbi-letter';

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
     * @return int
     */
    public function handle()
    {
        Artisan::call('db:seed', ['--class' => GenerateNbiLetter::class]);
    }
}

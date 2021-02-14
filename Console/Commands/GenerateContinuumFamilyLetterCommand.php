<?php

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateContinuumFamilyCareLetter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateContinuumFamilyLetterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:continuum-practice-letter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call GenerateContinuumFamilyCareLetter Seeder.';

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
        Artisan::call('db:seed', ['--class' => GenerateContinuumFamilyCareLetter::class]);
    }
}

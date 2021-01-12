<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class FixMakeJsonNullable extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp_invalid_json_ccdas:nulljson';

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
        \DB::table('temp_invalid_json_ccdas')->chunkById(1000, function ($ids) {
            Ccda::whereIn('id', $ids->pluck('id')->all())->update(['json' => null]);
        });
    }
}

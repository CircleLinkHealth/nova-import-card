<?php

namespace App\Console\Commands;

use App\Jobs\ConvertCcdaToJson;
use App\Models\MedicalRecords\Ccda;
use Illuminate\Console\Command;

class QueueCcdasToConvertToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccda:toJson';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'find CCDAs that have not yet been convert to json and convert them.';

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
        $ccdas = Ccda::where('json', '=', '')
            ->orWhereNull('json')
            ->take(10)
            ->get()
            ->map(function ($ccda) {
                dispatch(new ConvertCcdaToJson($ccda));
            });
    }
}

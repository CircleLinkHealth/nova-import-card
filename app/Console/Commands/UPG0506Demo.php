<?php

namespace App\Console\Commands;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\Incoming\Handlers\Pdf;
use App\Services\PhiMail\Incoming\Handlers\XML;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UPG0506Demo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:upg0506 {providerDm} {--pdf} {--ccd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper commands for UPG0506 demo';

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
        if ($this->option('pdf')) {
            $pdfDm = $this->createNewDemoDirectMessage();
            $pdf = new Pdf($pdfDm, file_get_contents(storage_path('files-for-demos/upg0506/upg0506-care-plan.pdf')));
            $pdf->handle();
            event(new DirectMailMessageReceived($pdfDm));
    
            $this->info('demo pdf sent');
        }
    
        if ($this->option('ccd')) {
            $ccdDm = $this->createNewDemoDirectMessage();
            $ccd = new XML($ccdDm, file_get_contents(storage_path('files-for-demos/upg0506/upg0506-ccda.xml')));
            $ccd->handle();
            event(new DirectMailMessageReceived($ccdDm));
    
            $this->info('demo ccd sent');
        }
    }
    
    /**
     * Creates a new Direct Message.
     *
     * @param CheckResult $message
     *
     * @return DirectMailMessage
     */
    private function createNewDemoDirectMessage()
    {
        return DirectMailMessage::create(
            [
                'message_id'      => Str::uuid(),
                'from'            => $this->argument('providerDm'),
                'to'              => config('services.emr-direct.user'),
                'body'            => 'This is a demo message.',
                'num_attachments' => collect([$this->option('ccd'), $this->option('pdf')])->filter()->count(),
            ]
        );
    }
}

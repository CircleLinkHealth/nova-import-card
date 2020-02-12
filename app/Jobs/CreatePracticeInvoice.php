<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\InvoicesCreatedNotification;
use App\Services\PracticeReportsService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePracticeInvoice implements ShouldQueue
{
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;
    
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var string
     */
    protected $date;
    /**
     * @var string
     */
    protected $format;
    /**
     * @var array
     */
    protected $practices;
    /**
     * @var int
     */
    protected $requestedByUserId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $practices, Carbon $date, string $format, int $requestedByUserId)
    {
        $this->practices         = $practices;
        $this->date              = $date;
        $this->format            = $format;
        $this->requestedByUserId = $requestedByUserId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PracticeReportsService $practiceReportsService)
    {
        $invoices = [];

        $user = User::findOrFail($this->requestedByUserId);

        if ('pdf' == $this->format) {
            $invoices = $practiceReportsService->getPdfInvoiceAndPatientReport($this->practices, $this->date);

            $user->notify(new InvoicesCreatedNotification(collect($invoices)->pluck('media.id')->all(), $this->date, $this->practices));

            return;
        }
        if ('csv' == $this->format || 'xls' == $this->format) {
            $report = $practiceReportsService->getQuickbooksReport(
                $this->practices,
                $this->format,
                $this->date
            );

            if (false === $report) {
                $user->notify(new InvoicesCreatedNotification([], $this->date, $this->practices));

                return;
            }

            $user->notify(new InvoicesCreatedNotification([$report->id], $this->date, $this->practices));

            return;
        }
    }
}

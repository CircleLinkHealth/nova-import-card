<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Jobs;

use CircleLinkHealth\CpmAdmin\Services\PracticeReportsService;
use Carbon\Carbon;
use CircleLinkHealth\CpmAdmin\Notifications\InvoicesCreatedNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePracticeInvoice implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var string
     */
    public $date;
    /**
     * @var string
     */
    public $format;
    /**
     * @var array
     */
    public $practices;
    /**
     * @var int
     */
    public $requestedByUserId;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

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
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     *
     * @return void
     */
    public function handle(PracticeReportsService $practiceReportsService)
    {
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $invoices = [];

        $user = User::findOrFail($this->requestedByUserId);

        if ('pdf' == $this->format) {
            $invoices = $practiceReportsService->getPdfInvoiceAndPatientReport($this->practices, $this->date);

            $user->notify(new InvoicesCreatedNotification(collect($invoices)->pluck('mediaIds')->flatten()->all(), $this->date, $this->practices));

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

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'CreatePracticeInvoice',
            'date:'.$this->date->toDateTimeString(),
            'format:'.$this->format,
            'practices:'.implode(',', $this->practices),
            'requestedByUserId:'.$this->requestedByUserId,
        ];
    }
}

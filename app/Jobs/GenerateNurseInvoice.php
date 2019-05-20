<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Billing\NurseMonthlyBillGenerator;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\Cache\View;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GenerateNurseInvoice implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private $addNotes;
    private $endDate;
    private $nurses;
    private $requestors;
    private $startDate;
    private $variablePay;

    /**
     * Create a new job instance.
     *
     * @param array          $nurseUserIds
     * @param Carbon         $startDate
     * @param Carbon         $endDate
     * @param Collection|int $requestors
     * @param $selectAll
     * @param bool   $variablePay
     * @param int    $addTime
     * @param string $addNotes
     */
    public function __construct(
        array $nurseUserIds,
        Carbon $startDate,
        Carbon $endDate,
        $requestors,
        bool $variablePay = false,
        string $addNotes = ''
    ) {//@todo: for selected all nurses option no need for the next query (it is already queried) how to i go for that?
        //.
        $this->nurses = Nurse::whereIn('user_id', $nurseUserIds)->with(['user',
            'summary' => function ($s) use ($startDate) {
                $s->where('month_year', $startDate->copy()->startOfMonth()->format('Y-m-d'));
            }, ])->get();
        $this->startDate   = $startDate;
        $this->endDate     = $endDate;
        $this->variablePay = $variablePay;
        $this->addNotes    = $addNotes;
        $this->requestors  = is_a($requestors, Collection::class)
            ? $requestors
            : collect($requestors);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        ini_set('max_execution_time', 420);
        ini_set('memory_limit', '512M');

        $data = $links = [];

        foreach ($this->nurses as $nurse) {
            $generator = (new NurseMonthlyBillGenerator(
                $nurse,
                $this->startDate,
                $this->endDate,
                $this->variablePay,
                $this->addNotes,
                $nurse->summary->first()
            ))
                ->handle();

            $data[] = $generator;

            $links[$nurse->user_id]['link'] = $generator['link'];
            $links[$nurse->user_id]['name'] = $generator['name'];
        }

        $viewHashKey = null;
        if ( ! empty($links) && ! empty($data)) {
            $viewHashKey = (new View())->storeViewInCache('billing.nurse.list', [
                'invoices' => $links,
                'data'     => $data,
                'month'    => $this->startDate->format('F'),
            ]);
        }

        $this->requestors->map(function ($userId) use ($links, $data, $viewHashKey) {
            $userNotification = new UserNotificationList($userId);

            if (empty($links) && empty($data)) {
                $userNotification->push('There was no data for Nurse Invoices.');

                return;
            }

            $userNotification->push(
                'Nurse Invoices',
                "Invoice(s) were generated for {$this->nurses->count()} nurse(s): {$this->nurses->map(function ($n) {
                    return $n->user->getFullName();
                })->implode(', ')}",
                linkToCachedView($viewHashKey),
                'Go to page'
            );
        });
    }
}

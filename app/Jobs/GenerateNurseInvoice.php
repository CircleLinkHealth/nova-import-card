<?php

namespace App\Jobs;

use App\Billing\NurseMonthlyBillGenerator;
use App\Nurse;
use App\Repositories\Cache\UserNotificationList;
use App\Repositories\Cache\View;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GenerateNurseInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $startDate;
    private $nurses;
    private $endDate;
    private $variablePay;
    private $addNotes;
    private $addTime;
    private $requestors;

    /**
     * Create a new job instance.
     *
     * @param array $nurseUserIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Collection|int $requestors
     * @param bool $variablePay
     * @param int $addTime
     * @param string $addNotes
     */
    public function __construct(array $nurseUserIds,
                                Carbon $startDate,
                                Carbon $endDate,
                                $requestors,
                                bool $variablePay = false,
                                int $addTime = 0,
                                string $addNotes = '')
    {
        $this->nurses = Nurse::whereIn('user_id', $nurseUserIds)->with('user')->get();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->variablePay = $variablePay;
        $this->addTime = $addTime;
        $this->addNotes = $addNotes;
        $this->requestors = is_a($requestors, Collection::class)
            ? $requestors
            : collect($requestors);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $links = [];

        foreach ($this->nurses as $nurse) {
            $generator = (new NurseMonthlyBillGenerator(
                $nurse,
                $this->startDate,
                $this->endDate,
                $this->variablePay,
                $this->addTime,
                $this->addNotes
            ))
                ->handle();

            $data[] = $generator;

            $links[$nurse->user_id]['link'] = $generator['link'];
            $links[$nurse->user_id]['name'] = $generator['name'];
        }

        $viewHashKey = null;
        if (!empty($links) && !empty($data)) {
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

            $userNotification->push('Nurse Invoices',
                "Invoice(s) were generated for {$this->nurses->count()} nurse(s): {$this->nurses->map(function($n) {return $n->user->fullName;})->implode(', ')}",
                route('get.cached.view.by.key', ['key' => $viewHashKey]),
                'Go to page'
            );
        });

    }
}

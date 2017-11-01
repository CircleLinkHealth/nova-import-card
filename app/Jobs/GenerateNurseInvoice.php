<?php

namespace App\Jobs;

use App\Billing\NurseMonthlyBillGenerator;
use App\Nurse;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateNurseInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $startDate;
    private $nurses;
    private $endDate;
    private $variablePay;
    private $addNotes;
    private $addTime;
    private $requestor;

    /**
     * Create a new job instance.
     *
     * @param array $nurseIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param bool $variablePay
     * @param int $addTime
     * @param string $addNotes
     * @param User $requestor
     */
    public function __construct(array $nurseIds,
                                Carbon $startDate,
                                Carbon $endDate,
                                bool $variablePay = false,
                                int $addTime = 0,
                                string $addNotes = '',
                                User $requestor)
    {
        $this->nurses = Nurse::whereIn('user_id', $nurseIds)->get();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->variablePay = $variablePay;
        $this->addTime = $addTime;
        $this->addNotes = $addNotes;
        $this->requestor = $requestor;
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

        $key = 'view' . str_random('20');


        if (empty($links) && empty($data)) {
            \Redis::rpush("user{$this->requestor->id}views", [
                'key'        => $key,
                'created_at' => Carbon::now()->toDateTimeString(),
                'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
                'view'       => false,
                'message'    => 'There was an error when compiling the reports. Please try again, and if the error persists, notify CLH.',
                'data'       => [],
            ]);

            return;
        }

        \Cache::put($key, [
            'key'        => $key,
            'view'       => 'billing.nurse.list',
            'message'    => 'The Nurse Invoices you requested are ready!',
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'data'       => [
                'invoices' => $links ?? [],
                'data'     => $data ?? [],
                'month'    => Carbon::parse($this->startDate)->format('F'),
            ],
        ], 11000);

        \Redis::rpush("user{$this->requestor->id}views", [
            'key'        => $key,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'view'       => 'billing.nurse.list',
            'message'    => 'The Nurse Invoices you requested are ready!',
            'data'       => [
                'invoices' => $links ?? [],
                'data'     => $data ?? [],
                'month'    => Carbon::parse($this->startDate)->format('F'),
            ],
        ]);
    }
}

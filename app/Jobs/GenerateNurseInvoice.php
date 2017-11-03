<?php

namespace App\Jobs;

use App\Billing\NurseMonthlyBillGenerator;
use App\Nurse;
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
    private $key;

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
        $this->nurses = Nurse::whereIn('user_id', $nurseUserIds)->get();
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->variablePay = $variablePay;
        $this->addTime = $addTime;
        $this->addNotes = $addNotes;
        $this->requestors = is_a($requestors, Collection::class)
            ? $requestors
            : collect($requestors);
        $this->key = 'view' . str_random('20');
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


        if (empty($links) && empty($data)) {
            $this->storeFailResponse();

            return;
        }

        $this->storeViewInCache('billing.nurse.list', [
            'invoices' => $links,
            'data'     => $data,
            'month'    => Carbon::parse($this->startDate)->format('F'),
        ]);

        $this->storeSuccessResponse();

    }

    /**
     * Store a fail response in the requesting User's view cache, basically notifying the User that this job failed.
     */
    public function storeFailResponse()
    {
        $this->requestors->map(function ($userId) {
            $message = 'There was an error when compiling the reports. Please try again, and if the error persists, notify CLH.';

            \Redis::rpush($this->getHashKeyForUser($userId), $this->userCachedNotificationFactory($message));
        });
    }

    /**
     * Get the hash key for the give User's cached views list
     *
     * @param $userId
     *
     * @return string
     */
    public function getHashKeyForUser($userId)
    {
        return "user:{$userId}:views";
    }

    /**
     * Create a User view
     *
     * @param $message
     * @param bool $view
     * @param array $data
     *
     * @return array
     */
    public function userCachedNotificationFactory($message)
    {
        return json_encode([
            'key'        => $this->key,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'message'    => $message,
        ]);
    }

    /**
     * Store a fail response in the requesting User's view cache, basically notifying the User that this job failed.
     */
    public function storeViewInCache($view, $data)
    {
        \Cache::put($this->key, [
            'view'       => $view,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString(),
            'data'       => $data,
        ], 11000);
    }

    /**
     * Store a success response in the requesting User's view cache, basically notifying the User that this job was
     * completed successfully.
     */
    public function storeSuccessResponse()
    {
        $message = 'Nurse Invoices';

        $this->requestors->map(function ($userId) use ($message) {
            \Redis::rpush(
                $this->getHashKeyForUser($userId),
                $this->userCachedNotificationFactory($message)
            );
        });
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Horizon\Contracts\JobRepository;

class HireFireController extends Controller
{
    /**
     * @var JobRepository
     */
    protected $jobs;

    /**
     * HireFireController constructor.
     */
    public function __construct(JobRepository $jobs)
    {
        $this->jobs = $jobs;
    }

    public function getQueueSize(Request $request, $token)
    {
        if ( ! $token === config('hirefire.token')) {
            abort(403);
        }

        return response()->json(
            [
                ['name' => 'worker', 'quantity' => $this->countPendingJobs($request)],
            ]
        );
    }

    private function countPendingJobs(Request $request)
    {
        return $this->getRecentJobs($request)->where('status', 'pending')->count();
    }

    private function getRecentJobs(Request $request)
    {
        return $this->jobs->getRecent($request->query('starting_at', -1))->map(
            function ($job) {
                $job->payload = json_decode($job->payload);

                return $job;
            }
        )->values();
    }
}

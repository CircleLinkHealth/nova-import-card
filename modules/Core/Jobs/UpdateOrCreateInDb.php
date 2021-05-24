<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateOrCreateInDb implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    protected array $attributes;

    protected string $model;
    protected $params;
    /**
     * @var callable|null
     */
    protected $passResultToJob;
    protected array $values;

    /**
     * Create a new job instance.
     *
     * @param  mixed|null $after
     * @param  mixed|null $passResultToJob
     * @return void
     */
    public function __construct(string $model, array $attributes, array $values = [], $passResultToJob = null, ...$params)
    {
        $this->model           = $model;
        $this->attributes      = $attributes;
        $this->values          = $values;
        $this->passResultToJob = $passResultToJob;
        $this->params          = $params;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        return [60, 180, 300];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("UpdateOrCreate[{$this->model}]");

        $model = $this->model::updateOrCreate($this->attributes, empty($this->values) ? $this->attributes : $this->values);

        if ( ! is_null($this->passResultToJob)) {
            $this->passResultToJob::dispatch($model, ...$this->params);
        }
    }
}

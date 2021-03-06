<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem.
 *
 * @property int                                                   $id
 * @property int                                                   $monitored_scheduled_task_id
 * @property string                                                $type
 * @property array|null                                            $meta
 * @property \Illuminate\Support\Carbon|null                       $created_at
 * @property \Illuminate\Support\Carbon|null                       $updated_at
 * @property \Spatie\ScheduleMonitor\Models\MonitoredScheduledTask $monitoredScheduledTask
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|MonitoredScheduledTaskLogItem newModelQuery()
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|MonitoredScheduledTaskLogItem newQuery()
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|MonitoredScheduledTaskLogItem query()
 * @mixin \Eloquent
 */
class MonitoredScheduledTaskLogItem extends Model
{
    public const TYPE_FAILED   = 'failed';
    public const TYPE_FINISHED = 'finished';
    public const TYPE_SKIPPED  = 'skipped';

    public const TYPE_STARTING = 'starting';

    public $casts = [
        'meta' => 'array',
    ];

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('schedule-monitor.tasks_log_items_db_table');
    }

    public function monitoredScheduledTask(): BelongsTo
    {
        return $this->belongsTo(MonitoredScheduledTask::class);
    }

    public function updateMeta(array $values): self
    {
        $this->update(['meta' => $values]);

        return $this;
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Spatie\ScheduleMonitor\Support\ScheduledTasks\Tasks;

use Carbon\CarbonInterface;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Lorisleiva\CronTranslator\CronParsingException;
use Lorisleiva\CronTranslator\CronTranslator;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

abstract class Task
{
    protected Event $event;

    protected ?MonitoredScheduledTask $monitoredScheduledTask = null;

    protected string $uniqueId;

    public function __construct(Event $event)
    {
        $this->event = $event;

        $this->uniqueId = (string) Str::uuid();

        if ( ! empty($this->name())) {
            $this->monitoredScheduledTask = MonitoredScheduledTask::findByName($this->name());
        }
    }

    abstract public static function canHandleEvent(Event $event): bool;

    public function cronExpression(): string
    {
        return $this->event->getExpression();
    }

    abstract public function defaultName(): ?string;

    public function graceTimeInMinutes()
    {
        return $this->event->graceTimeInMinutes ?? 5;
    }

    public function humanReadableCron(): string
    {
        try {
            return CronTranslator::translate($this->cronExpression());
        } catch (CronParsingException $exception) {
            return $this->cronExpression();
        }
    }

    public function isBeingMonitored(): bool
    {
        return ! is_null($this->monitoredScheduledTask);
    }

    public function isBeingMonitoredAtOhDear(): bool
    {
        if ( ! $this->isBeingMonitored()) {
            return false;
        }

        return ! empty($this->monitoredScheduledTask->ping_url);
    }

    public function lastRunFailed(): bool
    {
        if ( ! $this->isBeingMonitored()) {
            return false;
        }

        if ( ! $lastRunFailedAt = $this->lastRunFailedAt()) {
            return false;
        }

        if ( ! $lastRunStartedAt = $this->lastRunStartedAt()) {
            return true;
        }

        return $lastRunFailedAt->isAfter($lastRunStartedAt->subSecond());
    }

    public function lastRunFailedAt(): ?CarbonInterface
    {
        return optional($this->monitoredScheduledTask)->last_failed_at;
    }

    public function lastRunFinishedAt(): ?CarbonInterface
    {
        return optional($this->monitoredScheduledTask)->last_finished_at;
    }

    public function lastRunFinishedTooLate(): bool
    {
        if ( ! $this->isBeingMonitored()) {
            return false;
        }

        $lastFinishedAt = $this->lastRunFinishedAt()
            ? $this->lastRunFinishedAt()
            : $this->monitoredScheduledTask->created_at;

        $expectedNextRunStart = $this->nextRunAt($lastFinishedAt->subSecond());

        $shouldHaveFinishedAt = $expectedNextRunStart->addMinutes($this->graceTimeInMinutes());

        return $shouldHaveFinishedAt->isPast();
    }

    public function lastRunSkippedAt(): ?CarbonInterface
    {
        return optional($this->monitoredScheduledTask)->last_skipped_at;
    }

    public function lastRunStartedAt(): ?CarbonInterface
    {
        return optional($this->monitoredScheduledTask)->last_started_at;
    }

    public function name(): ?string
    {
        return $this->event->monitorName ?? $this->defaultName();
    }

    public function nextRunAt(CarbonInterface $now = null): CarbonInterface
    {
        $dateTime = CronExpression::factory($this->cronExpression())->getNextRunDate(
            $now ?? now(),
            0,
            false,
            $this->timezone()
        );

        $date = Date::instance($dateTime);

        $date->setTimezone(config('app.timezone'));

        return $date;
    }

    public function previousRunAt(): CarbonInterface
    {
        $dateTime = CronExpression::factory($this->cronExpression())->getPreviousRunDate(now());

        return Date::instance($dateTime);
    }

    public function shouldMonitor(): bool
    {
        if ( ! isset($this->event->doNotMonitor)) {
            return true;
        }

        return ! $this->event->doNotMonitor;
    }

    public function timezone(): string
    {
        return (string) $this->event->timezone;
    }

    abstract public function type(): string;

    public function uniqueId(): string
    {
        return $this->uniqueId;
    }
}

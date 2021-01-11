<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Fluent;
use Scoutapm\Events\Span\SpanReference;
use Scoutapm\Laravel\Facades\ScoutApm;

trait ScoutMonitoredDispatchable
{
    /**
     * Dispatch the job with the given arguments.
     *
     * @return \Illuminate\Foundation\Bus\PendingDispatch
     */
    public static function dispatch()
    {
        return self::wrapWithScout(new PendingDispatch(new static(...func_get_args())));
    }

    /**
     * Dispatch a command to its appropriate handler after the current process.
     *
     * @return mixed
     */
    public static function dispatchAfterResponse()
    {
        return self::wrapWithScout(app(Dispatcher::class)->dispatchAfterResponse(new static(...func_get_args())));
    }

    /**
     * Dispatch the job with the given arguments if the given truth test passes.
     *
     * @param  bool                                                                  $boolean
     * @param  mixed                                                                 ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent
     */
    public static function dispatchIf($boolean, ...$arguments)
    {
        return $boolean
            ? self::wrapWithScout(new PendingDispatch(new static(...$arguments)))
            : self::wrapWithScout(new Fluent());
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @return mixed
     */
    public static function dispatchNow()
    {
        return self::wrapWithScout(app(Dispatcher::class)->dispatchNow(new static(...func_get_args())));
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * Queuable jobs will be dispatched to the "sync" queue.
     *
     * @return mixed
     */
    public static function dispatchSync()
    {
        return self::wrapWithScout(app(Dispatcher::class)->dispatchSync(new static(...func_get_args())));
    }

    /**
     * Dispatch the job with the given arguments unless the given truth test passes.
     *
     * @param  bool                                                                  $boolean
     * @param  mixed                                                                 ...$arguments
     * @return \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent
     */
    public static function dispatchUnless($boolean, ...$arguments)
    {
        return ! $boolean
            ? self::wrapWithScout(new PendingDispatch(new static(...$arguments)))
            : self::wrapWithScout(new Fluent());
    }

    /**
     * Set the jobs that should run if this job is successful.
     *
     * @param  array                                   $chain
     * @return \Illuminate\Foundation\Bus\PendingChain
     */
    public static function withChain($chain)
    {
        return self::wrapWithScout(new PendingChain(static::class, $chain));
    }

    public static function wrapWithScout($wrapThis)
    {
        return ScoutApm::instrument(
            'Job',
            self::class,
            static function (?SpanReference $span = null) use ($wrapThis) {
                return $wrapThis;
            }
        );
    }
}

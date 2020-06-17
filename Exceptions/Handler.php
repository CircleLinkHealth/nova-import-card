<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * A list of the exception types that should be recorded, but no notification should be sent.
     *
     * @var array
     */
    protected $recordButNotNotify = [
        SuspiciousOperationException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Throwable
     *
     * @return \Illuminate\Http\Response
     */
    public function render(
        $request,
        Exception $e
    ) {
        if ($e instanceof ModelNotFoundException) {
            return response($e->getMessage(), 400);
        }
        if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Report or log an exception.
     *
     * @throws Exception
     *
     * @return mixed|void
     */
    public function report(Exception $e)
    {
        if ( ! $this->shouldReport($e)) {
            return;
        }

        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1] ?? null;

            if (1062 == $errorCode) {
                //1062 means we violated some key constraint (eg. trying to enter a duplicate value on a column with a unique index)
                //we don't actually want to terminate the program if this happens
                //we just don't wanna add the row again
                return;
            }

            //Query exceptions may contain PHI, so we don't want to send them to bug trackers. We will quietly log it in the background and bail
//            @todo: decide if we want to implement fully
//            StorePHIException::dispatch($e);
//            return;
        }

        if (app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated(
        $request,
        AuthenticationException $exception
    ) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($exception->redirectTo()) {
            return redirect($exception->redirectTo());
        }

        return redirect()->guest('login');
    }
}

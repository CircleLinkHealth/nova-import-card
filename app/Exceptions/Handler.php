<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use LERN;
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
        ModelNotFoundException::class,
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
     * @param \Exception               $e
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
        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json(['token_expired'], $e->getStatusCode());
        }
        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        }
        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
            return response()->json(['token_blacklisted'], '403');
        }
        if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     *
     * @throws Exception
     */
    public function report(Exception $e)
    {
        if ($e instanceof \Illuminate\Database\QueryException) {
            $errorCode = $e->errorInfo[1];
            if (1062 == $errorCode) {
                //do nothing
                //we don't actually want to terminate the program if we detect duplicates
                //we just don't wanna add the row again
                \Log::alert($e);
            }
        }

        if ($this->shouldReport($e) && ! in_array(config('app.env'), [
            'local',
            'development',
            'dev',
            'testing',
        ])
        ) {
            //Check to see if LERN is installed otherwise you will not get an exception.
            if (app()->bound('lern')) {
                LERN::pushHandler(
                    new \Monolog\Handler\SlackWebhookHandler(
                        config('lern.notify.slack.webhook'),
                        config('lern.notify.slack.channel'),
                        config('lern.notify.slack.username'),
                        true,
                        null,
                        false
                    )
                );

                if ($this->shouldRecordOnly($e)) {
                    app()->make('lern')->record($e);
                } else {
                    //Record and Notify the Exception
                    app()->make('lern')->handle($e);
                }

                /*
                OR...
                app()->make("lern")->record($e); //Record the Exception to the database
                app()->make("lern")->notify($e); //Notify the Exception
                */
            }
        }

        return parent::report($e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
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

        return redirect()->guest('login');
    }

    private function shouldRecordOnly($e)
    {
        return in_array(get_class($e), $this->recordButNotNotify);
    }
}

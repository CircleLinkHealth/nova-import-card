<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class ValidationException extends Exception
{
    /**
     * The name of the error bag.
     *
     * @var string
     */
    public $errorBag;

    /**
     * The path the client should be redirected to.
     *
     * @var string
     */
    public $redirectTo;

    /**
     * The recommended response to send to the client.
     *
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    public $response;

    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 422;
    /**
     * The validator instance.
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    public $validator;

    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator      $validator
     * @param  \Symfony\Component\HttpFoundation\Response|null $response
     * @param  string                                          $errorBag
     * @return void
     */
    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct('The given data was invalid. Check fields '.implode(', ', array_keys($validator->failed())));

        $this->response  = $response;
        $this->errorBag  = $errorBag;
        $this->validator = $validator;
    }

    /**
     * Set the error bag on the exception.
     *
     * @param  string $errorBag
     * @return $this
     */
    public function errorBag($errorBag)
    {
        $this->errorBag = $errorBag;

        return $this;
    }

    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors()
    {
        return $this->validator->errors()->messages();
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the URL to redirect to on a validation error.
     *
     * @param  string $url
     * @return $this
     */
    public function redirectTo($url)
    {
        $this->redirectTo = $url;

        return $this;
    }

    /**
     * Set the HTTP status code to be used for the response.
     *
     * @param  int   $status
     * @return $this
     */
    public function status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Create a new validation exception from a plain array of messages.
     *
     * @return \Illuminate\Validation\ValidationException
     */
    public static function withMessages(array $messages)
    {
        return new static(tap(ValidatorFacade::make([], []), function ($validator) use ($messages) {
            foreach ($messages as $key => $value) {
                foreach (Arr::wrap($value) as $message) {
                    $validator->errors()->add($key, $message);
                }
            }
        }));
    }
}

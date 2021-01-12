<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\RedirectToVaporRequest;
use Illuminate\Foundation\Http\FormRequest;

class LoginFromHerokuRequest extends FormRequest
{
    /**
     * @var mixed
     */
    private RedirectToVaporRequest $loginRequest;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->findRequest();
    }

    /**
     * @return mixed
     */
    public function getLoginRequest()
    {
        return $this->loginRequest;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }

    private function findRequest()
    {
        $this->loginRequest = RedirectToVaporRequest::where('token', $this->token)->first();

        return (bool) $this->loginRequest;
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DownloadZippedMediaWithSignedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->id() === (int) $this->route('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = User::where('id', $value)->exists();
                    if ( ! $exists) {
                        $fail('Invalid User.');
                    }
                },
            ],
            'media_ids' => [
                'required',
                Rule::exists('media', 'id'),
            ],
        ];
    }

    public function validationData()
    {
        return $this->route()->parameters();
    }
}

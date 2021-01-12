<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Requests;

use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DownloadMediaWithSignedRequest extends FormRequest
{
    /**
     * @var Media
     */
    private $media;
    /**
     * @var Practice
     */
    private $practice;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->id() === (int) $this->route('user_id');
    }

    public function rules()
    {
        return [
            'practice_id' => [
                'required',
                Rule::exists('practices', 'id'),
            ],
            'user_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = User::ofPractice($this->route('practice_id'))
                        ->where('id', $value)->exists();
                    if ( ! $exists) {
                        $fail('Invalid User.');
                    }
                },
            ],
            'media_id' => [
                'required',
                Rule::exists('media', 'id')
                    ->where('model_type', Practice::class),
            ],
        ];
    }

    public function validationData()
    {
        return $this->route()->parameters();
    }
}

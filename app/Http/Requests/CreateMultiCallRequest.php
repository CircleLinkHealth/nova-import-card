<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMultiCallRequest extends FormRequest
{
    use Traits\ValidatesNewCall;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return collect($this->newCallValidationRules())
            ->transform(fn ($val, $key) => $key = "*.$key")
            ->all();
    }
}

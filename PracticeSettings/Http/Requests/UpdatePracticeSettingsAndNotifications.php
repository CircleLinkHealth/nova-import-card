<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\PracticeSettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePracticeSettingsAndNotifications extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'invoice_recipients'       => 'email_array',
            'weekly_report_recipients' => 'email_array',
        ];
    }
}

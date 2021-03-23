<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PracticeInvoiceControllerMakeInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'      => 'required|date',
            'format'    => 'required|string',
            'practices' => 'required|array',
        ];
    }
}

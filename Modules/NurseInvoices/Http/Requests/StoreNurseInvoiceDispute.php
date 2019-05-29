<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Requests;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNurseInvoiceDispute extends FormRequest
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
            'invoiceId' => ['required', Rule::exists((new NurseInvoice())->getTable(), 'id')->where(function ($query) {
                $query->where('nurse_info_id', optional(\Auth::user()->nurseInfo)->id);
            })],
            'reason' => 'required|string|min:10',
        ];
    }
}

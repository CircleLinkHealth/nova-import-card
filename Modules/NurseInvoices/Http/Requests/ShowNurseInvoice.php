<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Http\Requests;

use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Foundation\Http\FormRequest;

class ShowNurseInvoice extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->isAdmin() || NurseInvoice::where([
            ['nurse_info_id', '=', $this->route('nurse_info_id')],
            ['id', '=', $this->route('invoice_id')],
        ])->ofNurses(auth()->user())->exists();
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
}

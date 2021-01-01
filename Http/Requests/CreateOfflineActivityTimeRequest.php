<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Requests;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOfflineActivityTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return optional(auth()->user())->hasPermission('offlineActivityRequest.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->merge(
            [
                'patient_id' => $this->route('patientId'),
            ]
        );

        return [
            'type'                  => ['required', Rule::in(Activity::input_activity_types())],
            'duration_minutes'      => ['required', 'numeric'],
            'performed_at'          => ['required', 'date'],
            'comment'               => ['required', 'string'],
            'chargeable_service_id' => ['required', Rule::exists((new ChargeableService())->getTable(), 'id')],
            'patient_id'            => [Rule::exists((new User())->getTable(), 'id')],
        ];
    }
}

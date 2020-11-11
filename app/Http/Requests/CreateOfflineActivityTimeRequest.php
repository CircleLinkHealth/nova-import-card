<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
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
            'is_behavioral'         => ['boolean'],
            'performed_at'          => ['required', 'date'],
            'comment'               => ['required', 'string'],
            'patient_id'            => [Rule::exists((new User())->getTable(), 'id')],
            'chargeable_service_id' => [Rule::exists((new ChargeableService())->getTable(), 'id')],
        ];
    }
}

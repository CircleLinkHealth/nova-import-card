<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Requests;

use CircleLinkHealth\Customer\Policies\CreateNoteForPatient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;

class CreateNewCallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        
        if ($user->isCareCoach()) {
            return app(CreateNoteForPatient::class)->can($user->id, $this->route('patientId') ?? $this->input('0.patientId'));
        }
        
        return $user->hasPermission('call.create');
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'inbound_cpm_id'   => 'required_if:*.inbound_cpm_id,null|filled',
            '*.inbound_cpm_id' => 'required_if:inbound_cpm_id,null|filled',
        ];
    }
}

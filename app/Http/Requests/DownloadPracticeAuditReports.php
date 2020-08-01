<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\Constants;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DownloadPracticeAuditReports extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'practice_id' => [
                'required',
                'numeric',
                Rule::exists('practice_role_user', 'program_id')->where(
                    function ($query) {
                        $query->where('user_id', auth()->id())
                            ->where('program_id', $this->input('practice_id'))
                            ->whereIn(
                                'role_id',
                                Role::whereIn('name', array_merge(Constants::PRACTICE_STAFF_ROLE_NAMES, ['administrator']))->pluck('id')->all()
                            );
                    }
                ),
            ],
            'month' => 'required|date',
        ];
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Requests;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class ImportEnrolleesFromSelfEnrollmentRequest extends FormRequest
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
            'enrolleeId' => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $input = $validator->getData();
            $enrolleeId = intval($input['enrolleeId']);
            $enrollee = Enrollee::find($enrolleeId);

            if ( ! $enrollee) {
                $message = "Enrollee [$enrolleeId] missing from CPM during importing from SelfEnrollment.";
                Log::error($message);
                sendSlackMessage('#self_enrollment_logs', $message);

                return;
            }

            $this->request->add([
                'enrolleeId' => $enrolleeId,
            ]);
        });
    }
}

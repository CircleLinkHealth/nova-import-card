<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\CareAmbassadorLog;
use App\Http\Resources\Enrollable;
use App\SafeRequest as Request;
use App\Services\Enrollment\AttachEnrolleeFamilyMembers;
use App\Services\Enrollment\EnrollableCallQueue;
use App\Services\Enrollment\SuggestEnrolleeFamilyMembers;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Support\Str;

class EnrollmentCenterController extends ApiController
{
    public function consented(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollee = Enrollee::find($request->input('enrollable_id'));

        AttachEnrolleeFamilyMembers::attach($request);

        //update report for care ambassador:
        $report              = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_enrolled = $report->no_enrolled + 1;
        $report->total_calls = $report->total_calls + 1;
//        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->setHomePhoneAttribute($request->input('home_phone'));
        $enrollee->setCellPhoneAttribute($request->input('cell_phone'));
        $enrollee->setOtherPhoneAttribute($request->input('other_phone'));

        //set preferred phone
        switch ($request->input('preferred_phone')) {
            case 'home':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('home_phone'));
                break;
            case 'cell':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('cell_phone'));
                break;
            case 'other':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('other_phone'));
                break;
            case 'agent':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('agent_phone'));
                $enrollee->agent_details = [
                    Enrollee::AGENT_PHONE_KEY        => $request->input('agent_phone'),
                    Enrollee::AGENT_NAME_KEY         => $request->input('agent_name'),
                    Enrollee::AGENT_EMAIL_KEY        => $request->input('agent_email'),
                    Enrollee::AGENT_RELATIONSHIP_KEY => $request->input('agent_relationship'),
                ];
                break;
            default:
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('home_phone'));
        }

        $enrollee->address                 = $request->input('address');
        $enrollee->address_2               = $request->input('address_2');
        $enrollee->state                   = $request->input('state');
        $enrollee->city                    = $request->input('city');
        $enrollee->zip                     = $request->input('zip');
        $enrollee->email                   = $request->input('email');
        $enrollee->dob                     = $request->input('dob');
        $enrollee->last_call_outcome       = $request->input('consented');
        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

//        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->attempt_count = $enrollee->attempt_count + 1;

        $enrollee->other_note = $request->input('extra');

        if (is_array($request->input('days'))) {
            $enrollee->preferred_days = collect($request->input('days'))->reject(function ($d) {
                return 'all' == $d;
            })->implode(', ');
        }

        if (is_array($request->input('times'))) {
            $enrollee->preferred_window = createTimeRangeFromEarliestAndLatest($request->input('times'));
        }

        $enrollee->status          = Enrollee::CONSENTED;
        $enrollee->consented_at    = Carbon::now()->toDateTimeString();
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        $queue = explode(',', $request->input('queue'));
        $queue = collect(array_merge(
            $queue,
            explode(',', $request->input('confirmed_family_members'))
        ))->unique()->toArray();
        if ( ! empty($queue) && in_array($enrollee->id, $queue)) {
            unset($queue[array_search($enrollee->id, $queue)]);
        }

        ImportConsentedEnrollees::dispatch([$enrollee->id], $enrollee->batch);

        EnrollableCallQueue::update($careAmbassador, $enrollee, $request->input('confirmed_family_members'));

        return response()->json([
            'status' => 200,
        ]);
    }

    public function getSuggestedFamilyMembers($enrolleeId)
    {
        return $this->json([
            'suggested_family_members' => SuggestEnrolleeFamilyMembers::get((int) $enrolleeId),
        ]);
    }

    public function queryEnrollables(Request $request)
    {
        $input = $request->all();

        if ( ! array_key_exists('enrollables', $input)) {
            return response()->json([], 400);
        }

        $searchTerms = explode(' ', $input['enrollables']);

        $query = Enrollee::with(['practice.enrollmentTips', 'provider.providerInfo'])
            ->shouldBeCalled()
            ->where('care_ambassador_user_id', auth()->user()->id);

        foreach ($searchTerms as $term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%${term}%")
                    ->orWhere('last_name', 'like', "%${term}%")
                    ->orWhere(function ($query) use ($term) {
                        $query->hasPhone($term);
                    });
            });
        }

        $results     = $query->get();
        $enrollables = [];
        $i           = 0;
        foreach ($results as $e) {
            $matchingPhones = collect([]);

            foreach ($searchTerms as $term) {
                //remove dashes for e164 format
                $sanitizedTerm = trim(str_replace('-', '', $term));
                if (Str::contains($e->home_phone_e164, $sanitizedTerm)) {
                    $matchingPhones->push($e->home_phone);
                }
                if (Str::contains($e->cell_phone_e164, $sanitizedTerm)) {
                    $matchingPhones->push($e->cell_phone);
                }
                if (Str::contains($e->other_phone_e164, $sanitizedTerm)) {
                    $matchingPhones->push($e->other_phone);
                }
            }

            if ($matchingPhones->isEmpty()) {
                $matchingPhones = collect([
                    $e->home_phone,
                    $e->cell_phone,
                    $e->other_phone,
                ]);
            }

            $phonesString = $matchingPhones->unique()->implode(', ');

            $enrollables[$i]['id']       = $e->id;
            $enrollables[$i]['name']     = $e->first_name.' '.$e->last_name;
            $enrollables[$i]['mrn']      = $e->mrn;
            $enrollables[$i]['program']  = optional($e->practice)->display_name ?? '';
            $enrollables[$i]['provider'] = optional($e->provider)->getFullName() ?? '';
            $enrollables[$i]['hint']     = $enrollables[$i]['name'].'PROVIDER:  ['.$enrollables[$i]['program']."] HOME PHONE: {$e->home_phone}";
            $enrollables[$i]['hint']     = "{$enrollables[$i]['name']} {$phonesString} PROVIDER: [{$enrollables[$i]['provider']}] [{$enrollables[$i]['program']}]";
            ++$i;
        }

        return response()->json($enrollables);
    }

    public function rejected(Request $request)
    {
        $enrollee       = Enrollee::find($request->input('enrollable_id'));
        $careAmbassador = auth()->user()->careAmbassador;

        AttachEnrolleeFamilyMembers::attach($request);

        //soft_rejected or rejected
        $status = $request->input('status', Enrollee::REJECTED);

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);

        if (Enrollee::REJECTED === $status) {
            $report->no_rejected = $report->no_rejected + 1;
        } else {
            $report->no_soft_rejected = $report->no_soft_rejected + 1;
        }

        $report->total_calls = $report->total_calls + 1;
//        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        $enrollee->status = $status;

        $enrollee->attempt_count   = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();
//        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->save();

        EnrollableCallQueue::update($careAmbassador, $enrollee, $request->input('confirmed_family_members'));

        return response()->json([
            'status' => 200,
        ]);
    }

    public function show($enrollableId = null)
    {
        $enrollable = $enrollableId ?
            Enrollee::whereCareAmbassadorUserId(auth()->user()->id)->find($enrollableId) :
            $enrollable = EnrollableCallQueue::getNext(
                auth()->user()->careAmbassador
            );

        if ( ! $enrollable) {
            return response()->json([
                'message' => 'No enrollables exist.',
            ]);
        }

        return Enrollable::make($enrollable);
    }

    public function unableToContact(Request $request)
    {
        $enrollee       = Enrollee::find($request->input('enrollable_id'));
        $careAmbassador = auth()->user()->careAmbassador;

        AttachEnrolleeFamilyMembers::attach($request);

        //update report for care ambassador:
        $report              = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_utc      = $report->no_utc + 1;
        $report->total_calls = $report->total_calls + 1;
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->other_note = $request->input('utc_note');

        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        if ('requested callback' == $request->input('reason')) {
            $enrollee->status = Enrollee::TO_CALL;
            if ($request->has('utc_callback')) {
                $enrollee->requested_callback = $request->input('utc_callback');
            }
        } else {
            $enrollee->status = Enrollee::UNREACHABLE;
        }

        $enrollee->attempt_count   = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        EnrollableCallQueue::update($careAmbassador, $enrollee, $request->input('confirmed_family_members'));

        return response()->json([
            'status' => 200,
        ]);
    }
}

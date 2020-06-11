<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\CareAmbassadorLog;
use App\Http\Resources\Enrollable;
use App\SafeRequest as Request;
use App\Services\Enrollment\EnrollableCallQueue;
use App\Services\Enrollment\SuggestEnrollable;
use App\Services\Enrollment\UpdateEnrollable;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnrollmentCenterController extends ApiController
{
    public function consented(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollable = UpdateEnrollable::update($request->input('enrollable_id'), collect($request->allSafe()));

        //update report for care ambassador:
        $report              = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_enrolled = $report->no_enrolled + 1;
        $report->total_calls = $report->total_calls + 1;
        $report->save();

        EnrollableCallQueue::update($careAmbassador, $enrollable, $request->input('confirmed_family_members'));

        return response()->json([
            'status' => 200,
        ]);
    }

    public function getSuggestedFamilyMembers($enrolleeId)
    {
        return $this->json([
            'suggested_family_members' => SuggestEnrollable::get((int) $enrolleeId),
        ]);
    }

    public function queryEnrollables(Request $request)
    {
        $input = $request->allSafe();

        if ( ! array_key_exists('enrollables', $input)) {
            return response()->json([], 400);
        }

        $searchTerms = explode(' ', $input['enrollables']);

        $query = Enrollee::with(['provider', 'practice'])
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
            $enrollables[$i]['hint']     = "{$enrollables[$i]['name']} {$phonesString} PROVIDER: [{$enrollables[$i]['provider']}] [{$enrollables[$i]['program']}]";
            ++$i;
        }

        return response()->json($enrollables);
    }

    public function rejected(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollable = UpdateEnrollable::update($request->input('enrollable_id'), collect($request->allSafe()));

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);

        //soft_rejected or rejected
        $status = $request->input('status', Enrollee::REJECTED);
        if (Enrollee::REJECTED === $status) {
            $report->no_rejected = $report->no_rejected + 1;
        } else {
            $report->no_soft_rejected = $report->no_soft_rejected + 1;
        }
        $report->total_calls = $report->total_calls + 1;
        $report->save();

        EnrollableCallQueue::update($careAmbassador, $enrollable, $request->input('confirmed_family_members'));

        return response()->json([
            'status' => 200,
        ]);
    }

    public function show(Request $request, $enrollableId = null)
    {
        //make sure that enrollee that caused error is removed from queue
        //so that CA can continue calling while we investigate
        if ($request->has('error_enrollable_id')) {
            $errorEnrollee = Enrollee::find($request->input('error_enrollable_id'));
            if ($errorEnrollee) {
                Log::critical("Something failed when performing CA action on enrollee {$errorEnrollee->id}.");
                //Chose Ineligible because:
                //It will remove from CA queue
                //Can still be viewed by pressing button on CA Director page
                $errorEnrollee->status = Enrollee::INELIGIBLE;
                $errorEnrollee->save();
            }
        }

        if ($enrollableId) {
            $enrollable = Enrollee::withCaPanelRelationships()
                ->whereCareAmbassadorUserId(auth()->user()->id)
                ->find($enrollableId);

            if ( ! $enrollable) {
                return response()
                    ->json([
                        'message' => 'Patient not found.',
                    ], 404);
            }
        } else {
            $enrollable = EnrollableCallQueue::getNext(
                auth()->user()->careAmbassador
            );
        }
        //return stats and cookie if enrollable does not exist
        if ( ! $enrollable) {
            $stats = EnrollableCallQueue::getCareAmbassadorPendingCallStatus(auth()->user()->id);

            return response()->json([
                'patients_pending' => $stats['patients_pending'],
                'next_attempt_at'  => $stats['next_attempt_at'],
            ]);
        }

        //Edge case but we don't want the dashboard to break and CAs having nowhere to go after that
        if ( ! $enrollable->provider) {
            Log::critical("Enrollee with id: {$enrollable->id}, does not have provider attached. Marking as ineligible and recommending investigation.");
            $enrollable->status = Enrollee::INELIGIBLE;
            $enrollable->save();

            return $this->show($request);
        }

        return Enrollable::make($enrollable);
    }

    public function unableToContact(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollable = UpdateEnrollable::update($request->input('enrollable_id'), collect($request->allSafe()));

        //update report for care ambassador:
        $report              = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_utc      = $report->no_utc + 1;
        $report->total_calls = $report->total_calls + 1;
        $report->save();

        EnrollableCallQueue::update($careAmbassador, $enrollable, $request->input('confirmed_family_members'));

        return response()->json([
            'status' => 200,
        ]);
    }
}

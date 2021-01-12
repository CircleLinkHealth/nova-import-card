<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Resources\Enrollable;
use App\ValueObjects\Enrollment\EnrolleeForCaPanel;
use CircleLinkHealth\Customer\Http\Requests\SafeRequest as Request;
use CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Services\Enrollment\EnrollableCallQueue;
use CircleLinkHealth\SharedModels\Services\Enrollment\SuggestEnrollable;
use CircleLinkHealth\SharedModels\Services\Enrollment\UpdateEnrollable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnrollmentCenterController extends ApiController
{
    public function consented(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollable = UpdateEnrollable::update($request->input('enrollable_id'), collect($request->allSafe()));

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

            $item = [
                'id'       => $e->id,
                'name'     => $e->first_name.' '.$e->last_name,
                'mrn'      => $e->mrn,
                'program'  => optional($e->practice)->display_name ?? '',
                'provider' => optional($e->provider)->getFullName() ?? '',
            ];

            $item['hint'] = "{$item['name']} {$phonesString} PROVIDER: [{$item['provider']}] [{$item['program']}]";

            $enrollables[] = $item;
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
        $this->handleErrorIfExists($request);

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

        if ( ! $enrollable) {
            $stats = EnrollableCallQueue::getCareAmbassadorPendingCallStatus(auth()->user()->id);

            return response()->json([
                'patients_pending' => $stats['patients_pending'],
                'next_attempt_at'  => $stats['next_attempt_at'],
            ]);
        }

        if ( ! $enrollable->shouldAppearInCaPanel()) {
            $this->handleIneligibleEnrollable($enrollable);

            return $this->show($request);
        }

        $enrollableData = EnrolleeForCaPanel::getArray($enrollable);

        return Enrollable::make($enrollableData);
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

    private function handleErrorIfExists(Request $request)
    {
        if ( ! $request->has('error_enrollable_id') && ! $request->has('error_on_previous_submit')) {
            return;
        }
        //make sure that enrollee that caused error is removed from queue
        //so that CA can continue calling while we investigate
        $errorEnrolleeId = $request->input('error_enrollable_id');
        $errorEnrollee   = Enrollee::find($errorEnrolleeId);
        //skip messing with consented/enrolled enrollees
        if ($errorEnrollee && ! in_array($errorEnrollee->status, [Enrollee::CONSENTED, Enrollee::ENROLLED])) {
            //Chose Ineligible because:
            //It will remove from CA queue
            //Can still be viewed by pressing button on CA Director page
            //send slack message we will be able to investigate immediately
            //and even restore previous status
            $errorEnrollee->status = Enrollee::INELIGIBLE;
            $errorEnrollee->save();
        }
        sendSlackMessage('#ca_panel_alerts', "Something went wrong while performing action on Enrollee with id: {$errorEnrolleeId}. \n Message: {$request->input('error_on_previous_submit')}", true);
    }

    private function handleIneligibleEnrollable(Enrollee $enrollable): void
    {
        $message = "Marking Enrollee with id: {$enrollable->id} as ineligible and recommending investigation.";
        Log::critical($message);
        sendSlackMessage('#ca_panel_alerts', $message);
        $enrollable->status = Enrollee::INELIGIBLE;
        $enrollable->save();
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Filters\UserFilters;
use App\Http\Controllers\Controller;
use App\Location;
use App\Practice;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PracticeController extends Controller
{
    public function allPracticesWithLocationsAndStaff()
    {
        $practicesCollection = Practice::with('locations.providers')
            ->get([
                'id',
                'display_name',
            ]);

        //fixing up the data for vue. basically keying locations and providers by id
        $practices = $practicesCollection->keyBy('id')
            ->map(function ($practice) {
                return [
                    'id'           => $practice->id,
                    'display_name' => $practice->display_name,
                    'locations'    => $practice->locations->map(function ($loc) {
                        //is there no better way to do this?
                        $loc = new Collection($loc);

                        $loc['providers'] = collect($loc['providers'])->keyBy('id');

                        return $loc;
                    })->keyBy('id'),
                ];
            });

        return response()->json($practices->all());
    }

    /**
     * get providers within a location.
     */
    public function getLocationProviders(Request $request)
    {
        $practiceId          = $request->route()->parameters()['practiceId'];
        $locationId          = $request->route()->parameters()['locationId'];
        $providersCollection = Location::where('id', $locationId)->get(['id'])->map(function ($location) {
            return collect($location['providers'])
                ->map(function ($provider) {
                    return [
                        'id'           => $provider->id,
                        'display_name' => $provider->display_name,
                    ];
                })->toArray();
        });
        $providers = $providersCollection->toArray();
        if (isset($providers) && sizeof($providers) > 0) {
            return response()->json($providers[0]);
        }

        return response()->json(['message' => 'location not found'], 404);
    }

    public function getNurses($practiceId)
    {
        $nurses = User::ofType('care-center')
            ->whereHas('nurseInfo', function ($q) {
                $q->where([
                    'status' => 'active',
                ]);
            })
            ->ofPractice($practiceId)
            ->with('nurseInfo.states')
            ->get([
                'id',
                'first_name',
                'last_name',
                'suffix',
                'city',
                'state',
            ])
            ->map(function ($nurse) {
                $info = $nurse->nurseInfo;
                $states = (
                              $info
                              ? $info->states
                              : new Collection()
                          )
                    ->map(function ($state) {
                        return $state->code;
                    });

                if ($nurse->state && ! $states->contains($nurse->state)) {
                    $states->push($nurse->state);
                }

                return [
                    'id'         => $nurse->id,
                    'first_name' => $nurse->getFirstName(),
                    'last_name'  => $nurse->getLastName(),
                    'suffix'     => $nurse->getSuffix(),
                    'full_name'  => $nurse->display_name ?? ($nurse->getFullName()),
                    'city'       => $nurse->city,
                    'state'      => $nurse->state,
                    'states'     => $states,
                ];
            })
            ->toArray();

        return response()->json($nurses);
    }

    public function getPatients($practiceId)
    {
        $practice = Practice::find($practiceId);
        $patients = $practice->patients()->with('carePlan')->get([
            'id',
            'first_name',
            'last_name',
            'suffix',
            'city',
            'state',
        ])->map(function ($patient) {
            return [
                'id'         => $patient->id,
                'first_name' => $patient->getFirstName(),
                'last_name'  => $patient->getLastName(),
                'suffix'     => $patient->getSuffix(),
                'full_name'  => $patient->getFullName(),
                'city'       => $patient->city,
                'state'      => $patient->state,
                'status'     => optional($patient->carePlan)->status,
            ];
        })->toArray();

        return response()->json($patients);
    }

    /**
     * get locations within a practice.
     */
    public function getPracticeLocations(Request $request)
    {
        $practiceId         = $request->route()->parameters()['practiceId'];
        $locationCollection = Location::where('practice_id', $practiceId)->get([
            'id',
            'is_primary',
            'state',
            'name',
            'timezone',
            'practice_id',
        ])->map(function ($location) {
            return [
                'id'          => $location->id,
                'is_primary'  => $location->is_primary,
                'state'       => $location->state,
                'name'        => $location->name,
                'timezone'    => $location->timezone,
                'practice_id' => $location->practice_id,
                'providers'   => collect($location['providers'])->count(),
            ];
        });

        return response()->json($locationCollection->toArray());
    }

    /**
     * get providers within a practice.
     *
     * @param mixed $practiceId
     */
    public function getPracticeProviders($practiceId, UserFilters $filters)
    {
        return response()->json(User::ofType('provider')->ofPractice($practiceId)->filter($filters)->get()->map(function (
            $user
        ) {
            return $user->safe();
        }));
    }

    /**
     * get list of available practices.
     */
    public function getPractices()
    {
        $practicesCollection = auth()->user()
            ->practices(true, false)
            ->with('locations')
            ->get([
                'id',
                'display_name',
            ]);

        $practices = $practicesCollection
            ->map(function ($practice) {
                return [
                    'id'           => $practice->id,
                    'display_name' => $practice->display_name,
                    'locations'    => $practice->locations->count(),
                ];
            });

        return response()->json($practices->toArray());
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Practice;
use App\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use App\Filters\NurseFilters;

class PracticeController extends Controller
{
    public function allPracticesWithLocationsAndStaff() {
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
    * get list of available practices
    */
    public function getPractices() {
        $practicesCollection = auth()->user()
                                ->practices()
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
                    'locations'    => $practice->locations->count()
                ];
            });

        return response()->json($practices->toArray());
    }

    /**
    * get locations within a practice
    */
    public function getPracticeLocations(Request $request) {
        $practiceId = $request->route()->parameters()['practiceId'];
        $locationCollection = Location::where('practice_id', $practiceId)->get([
            'id','is_primary', 'state', 'name', 'timezone', 'practice_id'
        ])->map(function ($location) {
            return [
                'id'         => $location->id,
                'is_primary' => $location->is_primary,
                'state'      => $location->state,
                'name'       => $location->name,
                'timezone'   => $location->timezone,
                'practice_id'=> $location->practice_id,
                'providers' => collect($location['providers'])->count()
            ];
        });
        return response()->json($locationCollection->toArray());
    }
    
    /**
    * get providers within a location
    */
    public function getLocationProviders(Request $request) {
        $practiceId = $request->route()->parameters()['practiceId'];
        $locationId = $request->route()->parameters()['locationId'];
        $providersCollection = Location::where('id', $locationId)->get(['id'])->map(function ($location) {
            return collect($location['providers'])
                    ->map(function ($provider) {
                            return [
                                'id'    => $provider->id,
                                'display_name'  => $provider->display_name
                            ];
                    })->toArray();
        });
        $providers = $providersCollection->toArray();
        if (isset($providers) && sizeof($providers) > 0) {
            return response()->json($providers[0]);
        }
        else return response()->json([ 'message' => 'location not found' ], 404);
    }

    public function getPatients($practiceId) {
        $practice = Practice::find($practiceId);
        $patients = $practice->patients()->get([
            'id',
            'first_name',
            'last_name',
            'suffix',
            'city',
            'state'
        ])->map(function ($patient) {
            return [
                'id' =>  $patient->id,
                'first_name' =>  $patient->first_name,
                'last_name' =>  $patient->last_name,
                'suffix' =>  $patient->suffix,
                'full_name' => $patient->first_name . ' ' . $patient->last_name . ' ' . $patient->suffix,
                'city' =>  $patient->city,
                'state' =>  $patient->state
            ];
        })->toArray();
        return response()->json($patients);
    }
    
    public function getNurses($practiceId) {
        $practice = Practice::where('id', $practiceId)->get(['id'])->first();
        $nurses = $practice->nurses()->whereHas('nurseInfo', function ($q) {
            $q->where([
                'status' => 'active'
            ]);
        })->get([
            'id',
            'first_name',
            'last_name',
            'suffix',
            'city',
            'state'
        ])->map(function ($nurse) {
            $info = $nurse->nurseInfo()->first();
            $states = ($info ? $info->states()->get() : new Collection())->map(function ($state) {
                return $state->code;
            });
            if ($nurse->state && !$states->contains($nurse->state)) {
                $states->push($nurse->state);
            }
            return [
                'id' =>  $nurse->id,
                'first_name' =>  $nurse->first_name,
                'last_name' =>  $nurse->last_name,
                'suffix' =>  $nurse->suffix,
                'full_name' => $nurse->display_name ?? ($nurse->first_name . ' ' . $nurse->last_name . ' ' . $nurse->suffix),
                'city' =>  $nurse->city,
                'state' =>  $nurse->state,
                'states' => $states
            ];
        })->toArray();
        return response()->json($nurses);
    }
}

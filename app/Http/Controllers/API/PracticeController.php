<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Filters\UserFilters;
use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\PracticePatientsView;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $providersCollection = Location::where('practice_id', $practiceId)->whereHas('providers')->with('providers')->where('id', $locationId)->get(['id'])->map(function ($location) {
            return $location->providers
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
        $nurses = User::ofType(['care-center', 'care-center-external'])
            ->whereHas('nurseInfo', function ($q) {
                $q->where([
                    'status' => 'active',
                ]);
            })
            ->ofPractice($practiceId)
            ->with(['nurseInfo.states', 'roles'])
            ->when(auth()->user()->isCareCoach(), fn ($q) => $q->where('id', auth()->id()))
            ->get([
                'id',
                'first_name',
                'last_name',
                'suffix',
                'city',
                'state',
            ])
            ->map(function ($nurse) use ($practiceId) {
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
                    'id'    => $nurse->id,
                    'roles' => $nurse->rolesInPractice($practiceId)
                        ->map(function ($r) {
                            return $r->name;
                        }),
                    'first_name' => $nurse->getFirstName(),
                    'last_name'  => $nurse->getLastName(),
                    'suffix'     => $nurse->getSuffix(),
                    'full_name'  => $nurse->display_name ?? ($nurse->getFullName()),
                    'city'       => $nurse->city,
                    'state'      => $nurse->state,
                    'states'     => $states,
                    'spanish'    => $info->spanish,
                ];
            })
            ->toArray();

        return response()->json($nurses);
    }

    public function getPatients($practiceId)
    {
        $patients = PracticePatientsView::where('program_id', '=', $practiceId)
            ->get()
            ->map(function ($patient) {
                $firstName = ucfirst(strtolower($patient->first_name));
                $lastName = ucfirst(strtolower($patient->last_name));
                $suffix = $patient->suffix ?? '';
                $fullName = trim(ucwords("${firstName} ${lastName} ${suffix}"));

                return [
                    'id'                         => $patient->id,
                    'first_name'                 => $firstName,
                    'last_name'                  => $lastName,
                    'suffix'                     => $suffix,
                    'full_name'                  => $fullName,
                    'city'                       => $patient->city,
                    'state'                      => $patient->state,
                    'status'                     => $patient->status,
                    'ccm_status'                 => $patient->ccm_status,
                    'preferred_contact_language' => $patient->preferred_contact_language,
                ];
            })
            ->toArray();

        return response()->json($patients);
    }

    /**
     * get locations within a practice.
     */
    public function getPracticeLocations(Request $request)
    {
        $practiceId         = $request->route()->parameters()['practiceId'];
        $locationCollection = Location::where('practice_id', $practiceId)->with('providers')->get([
            'id',
            'is_primary',
            'state',
            'name',
            'timezone',
            'practice_id',
        ])
            ->map(function ($location) {
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
     * If called from admin pages, we have to fetch practices that user has admin rights to
     * If called from provider pages, we fetch all practices a user can view.
     */
    public function getPractices(Request $request)
    {
        //HTTP_REFERER is not always set by web servers, so we use the param in admin pages on client side
        $practicesIAmAdmin = $request->input('admin-only', false);
        if ( ! $practicesIAmAdmin
            && isset($_SERVER['HTTP_REFERER'])
            && Str::contains($_SERVER['HTTP_REFERER'], '/admin/')) {
            $practicesIAmAdmin = true;
        }
        $user = auth()->user();

        if ($practicesIAmAdmin) {
            $roleIds = $user->isAdmin()
                ? null
                : Role::getIdsFromNames(['software-only']);
        } else {
            $roleIds = null;
        }

        $practicesCollection = $user
            ->practices(true, false, $roleIds)
            ->with('locations')
            ->get([
                'practices.id',
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

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\ProviderInfo;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;

class ProviderInfoService
{
    public function getPatientProviders($userId)
    {
        $user = User::findOrFail($userId);

        return User::ofType('provider')
            ->with('providerInfo', 'phoneNumbers')
            ->intersectPracticesWith($user)
            ->get()
            ->transform(
                function ($u) {
                    return $u->safe();
                }
            );
    }

    public function list()
    {
        $providers = ProviderInfo::whereHas('user', function ($q) {
            $q->ofType('provider')
                ->when($isLoc = UserRepository::isLocationScopeProvider(auth()->user()), function ($q) {
                    $q->where('id', auth()->id());
                })
                ->when( ! $isLoc, function ($q) {
                    $q->ofPractice(auth()->user()->practices);
                });
        })
            ->select(['id', 'user_id', 'specialty'])
            ->orderBy('id', 'desc')->with(['user' => function ($q) {
                $q->select(['id', 'display_name', 'address']);
            }])
            ->get()
            ->transform(function ($p) {
                return [
                    'id'        => $p->id,
                    'user_id'   => $p->user_id,
                    'specialty' => $p->specialty,
                    'name'      => trim(optional($p->user)->display_name ?? ''),
                    'address'   => optional($p->user)->address,
                ];
            });

        return $providers;
    }

    public function provider($id)
    {
        $provider         = ProviderInfo::with('user.locations')->where(['user_id' => $id])->firstOrFail();
        $providerUser     = $provider->user;
        $provider['user'] = $this->setupProviderUser($providerUser);

        return $provider;
    }

    public function providers()
    {
        $providers = ProviderInfo::with('user')->orderBy('id', 'desc')->paginate();
        $providers->getCollection()->transform(function ($p) {
            $providerUser = $p->user;
            $p['user'] = $this->setupProviderUser($providerUser);

            return $p;
        });

        return $providers;
    }

    public function setupProviderUser($providerUser)
    {
        return [
            'id'           => $providerUser->id,
            'program_id'   => $providerUser->program_id,
            'display_name' => $providerUser->display_name,
            'address'      => $providerUser->address,
            'status'       => $providerUser->status,
            'locations'    => $providerUser->locations,
            'created_at'   => optional($providerUser->created_at)->format('c') ?? null,
            'updated_at'   => optional($providerUser->updated_at)->format('c') ?? null,
        ];
    }
}

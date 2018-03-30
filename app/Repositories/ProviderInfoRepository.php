<?php

namespace App\Repositories;

use App\User;
use App\ProviderInfo;
use Carbon\Carbon;

class ProviderInfoRepository
{
    public function model()
    {
        return app(ProviderInfo::class);
    }

    public function count() {
        return $this->model()->count();
    }

    public function exists($id) {
        return !!$this->model()->find($id);
    }

    public function list() {
        $program_id = auth()->user()->program_id;
        $providers = $this->model()
                            ->join('users', 'provider_info.user_id', '=', 'users.id')
                            ->where('users.program_id', $program_id)
                            ->orderBy('provider_info.id', 'desc')->with([ 'user' ])->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'specialty' => $p->specialty,
                'name' => optional($p->user)->display_name,
                'address' => optional($p->user)->address
            ];
        });
        return $providers;
    }

    public function setupProviderUser($providerUser) {
        return [
            'id' => $providerUser->id,
            'program_id' => $providerUser->program_id,
            'display_name' => $providerUser->display_name,
            'address' => $providerUser->address,
            'status' => $providerUser->status,
            'locations' => $providerUser->locations()->get(),
            'created_at' => optional($providerUser->created_at)->format('c') ?? null,
            'updated_at' => optional($providerUser->updated_at)->format('c') ?? null
        ];
    }

    public function providers() {
        $providers = $this->model()->orderBy('id', 'desc')->paginate();
        $providers->getCollection()->transform(function ($p) {
            $providerUser = $p->user()->first();
            $p['user'] = $this->setupProviderUser($providerUser);
            return $p;
        });
        return $providers;
    }

    public function provider($id) {
        $provider = $this->model()->where([ 'user_id' => $id ])->firstOrFail();
        $providerUser = $provider->user()->first();
        $provider['user'] = $this->setupProviderUser($providerUser);
        return $provider;
    }

    public function remove($id) {
        $this->model()->where([ 'id' => $id ])->delete();
        return [
            'message' => 'successful'
        ];
    }
}
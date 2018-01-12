<?php

namespace App\Repositories;

use App\User;
use App\ProviderInfo;

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

    public function providers() {
        return $this->model()->orderBy('id', 'desc')->paginate();
    }

    public function provider($id) {
        $provider = $this->model()->findOrFail($id);
        $providerUser = $provider->user()->first();
        $provider['user'] = [
            'id' => $providerUser->id,
            'program_id' => $providerUser->program_id,
            'display_name' => $providerUser->display_name,
            'address' => $providerUser->address,
            'status' => $providerUser->status,
            'locations' => $providerUser->locations()->get(),
            'created_at' => $providerUser->created_at->format('c'),
            'updated_at' => $providerUser->updated_at->format('c')
        ];
        return $provider;
    }

    public function remove($id) {
        $this->model()->where([ 'id' => $id ])->delete();
        return [
            'message' => 'successful'
        ];
    }
}
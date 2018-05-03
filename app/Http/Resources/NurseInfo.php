<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class NurseInfo extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $nurse = parent::toArray($request);
        if (array_key_exists('states', $nurse) && $request->has('compressed')) {
            $nurse['states'] = (new Collection($nurse['states']))->map(function ($s) {
                return $s['code'];
            });
            $user = User::find($nurse['user_id']);
            if ($user) {
                $nurse['practices'] = $user->practices()->get(['id'])->map(function ($p) {
                    return $p->id;
                });
            }
        }
        return $nurse;
    }
}

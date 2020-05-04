<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProviderRequest;
use App\User;
use Illuminate\Http\Request;

class ProviderController
{
    /**
     * Add a new provider.
     *
     * @param AddProviderRequest $request
     */
    public function add(AddProviderRequest $request)
    {
    }

    /**
     * Search for existing provider based on first name and last name.
     * NOTE: copied from CPM project: CareTeamController@searchProviders.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('name');

        $users = User
            ::ofType('provider')
            ->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%$searchTerm%")
                  ->orWhere('last_name', 'like', "%$searchTerm%");
            })
            ->with('primaryPractice')
            ->with('phoneNumbers')
            ->get();

        return response()->json(['results' => $users]);
    }
}

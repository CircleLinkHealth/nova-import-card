<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PracticeController
{
    public function search(Request $request)
    {
        $user = auth()->user();

        /** @var Collection $practicesCollection */
        $practicesCollection = $user
            ->practices(true)
            ->get([
                'id',
                'display_name',
            ])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'display_name' => $p->display_name,
                ];
            });

        $searchVal = $request->input('name', null);
        if ($searchVal) {
            $practicesCollection = $practicesCollection->filter(function ($p) use ($searchVal) {
                return stripos($p->display_name, $searchVal) > -1;
            });
        }

        return response()->json($practicesCollection->toArray());
    }
}

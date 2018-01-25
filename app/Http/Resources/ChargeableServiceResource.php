<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ChargeableServiceResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'description' => $this->description,
            'amount'      => $this->amount,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}

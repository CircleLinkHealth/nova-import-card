<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserAutocompleteResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name() ?? $this->display_name,
            'program_id' => $this->program_id,
            'status'    => optional($this->carePlan()->first())->status
        ];
    }
}

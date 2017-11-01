<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Note extends Resource
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
            'patient_id'           => $this->patient_id,
            'author_id'            => $this->author_id,
            'logger_id'            => $this->logger_id,
            'body'                 => $this->body,
            'isTCM'                => $this->isTCM,
            'type'                 => $this->type,
            'did_medication_recon' => $this->did_medication_recon,
            'performed_at'         => $this->performed_at,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Call extends Resource
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
            'id'                    => $this->id,
            'note_id'               => $this->note_id,
            'service'               => $this->service,
            'status'                => $this->status,
            'inbound_phone_number'  => $this->inbound_phone_number,
            'outbound_phone_number' => $this->outbound_phone_number,
            'inbound_cpm_id'        => $this->inbound_cpm_id,
            'outbound_cpm_id'       => $this->outbound_cpm_id,
            'call_time'             => $this->call_time,
            'created_at'            => $this->created_at ? $this->created_at->format('c') : null,
            'updated_at'            => $this->updated_at ? $this->updated_at->format('c') : null,
            'is_cpm_outbound'       => $this->is_cpm_outbound,
            'window_start'          => $this->window_start,
            'window_end'            => $this->window_end,
            'scheduled_date'        => $this->scheduled_date,
            'called_date'           => $this->called_date,
            'attempt_note'          => $this->attempt_note,
            'scheduler'             => $this->scheduler,

            'inbound_user'  => User::make($this->whenLoaded('inboundUser')),
            'outbound_user' => User::make($this->whenLoaded('outboundUser')),
            'note'          => Note::make($this->whenLoaded('note')),
        ];
    }
}

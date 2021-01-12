<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use CircleLinkHealth\Customer\Entities\User as UserModel;
use Illuminate\Http\Resources\Json\JsonResource;

class Call extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $schedulerName = $this->scheduler;
        if ((int) ($this->scheduler)) {
            $user = UserModel::find($this->scheduler);
            if ($user) {
                $schedulerName = $user->display_name;
            }
        }

        return [
            'id'                    => $this->id,
            'type'                  => $this->type,
            'sub_type'              => $this->sub_type,
            'is_from_care_center'   => $this->isFromCareCenter,
            'note_id'               => $this->note_id,
            'service'               => $this->service,
            'status'                => $this->status,
            'inbound_phone_number'  => $this->inbound_phone_number,
            'outbound_phone_number' => $this->outbound_phone_number,
            'inbound_cpm_id'        => $this->inbound_cpm_id,
            'outbound_cpm_id'       => $this->outbound_cpm_id,
            'call_time'             => $this->call_time,
            'created_at'            => $this->created_at
                ? $this->created_at->format('c')
                : null,
            'updated_at' => $this->updated_at
                ? $this->updated_at->format('c')
                : null,
            'is_cpm_outbound' => $this->is_cpm_outbound,
            'window_start'    => $this->window_start,
            'window_end'      => $this->window_end,
            'scheduled_date'  => $this->scheduled_date,
            'called_date'     => $this->called_date,
            'attempt_note'    => $this->attempt_note,
            'scheduler'       => $schedulerName,
            'is_manual'       => $this->is_manual,
            'asap'            => $this->asap,
            'sort_day'        => $this->sort_day ?? null,

            'inbound_user'  => UserResource::make($this->whenLoaded('inboundUser')),
            'outbound_user' => UserResource::make($this->whenLoaded('outboundUser')),
            'note'          => Note::make($this->whenLoaded('note')),
        ];
    }
}

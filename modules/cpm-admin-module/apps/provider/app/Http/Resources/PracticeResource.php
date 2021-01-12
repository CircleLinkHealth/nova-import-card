<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PracticeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                       => $this->id,
            'ehr_id'                   => $this->ehr_id,
            'user_id'                  => $this->user_id,
            'name'                     => $this->name,
            'display_name'             => $this->display_name,
            'active'                   => $this->active,
            'clh_pppm'                 => $this->clh_pppm,
            'term_days'                => $this->term_days,
            'federal_tax_id'           => $this->federal_tax_id,
            'same_clinical_contact'    => $this->same_clinical_contact,
            'same_ehr_login'           => $this->same_ehr_login,
            'sms_marketing_number'     => $this->sms_marketing_number,
            'weekly_report_recipients' => $this->weekly_report_recipients,
            'invoice_recipients'       => $this->invoice_recipients,
            'bill_to_name'             => $this->bill_to_name,
            'external_id'              => $this->external_id,
            'auto_approve_careplans'   => $this->auto_approve_careplans,
            'send_alerts'              => $this->send_alerts,
            'outgoing_phone_number'    => $this->outgoing_phone_number,
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
            'deleted_at'               => $this->deleted_at,
        ];
    }
}

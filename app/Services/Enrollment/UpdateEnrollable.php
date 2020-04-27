<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Collection;

class UpdateEnrollable extends EnrollableService
{
    protected $careAmbassador;
    
    protected $shouldUpdateAddress;
    
    protected $shouldUpdateAddress_2;
    
    public static function update(int $enrollableId, Collection $data)
    {
        if (!$data->has('enrollable_id')) {
            //throw exception
        }
        return (new static($enrollableId, $data))->updateEnrollable($data);
    }
    
    private function updateEnrollable(Collection $data)
    {
        $this->getModel();
        $this->careAmbassador = auth()->user()->careAmbassador;
        $this->data = $data;
        
        $this->updateEnrollableModel();
        
        $this->attachFamilyMembers();
        
        return $this->enrollee;
    }
    
    /**
     * This function must be AFTER updateEnrollable
     * So we can copy primary phone number and immediately attach to confirm family members
     * And we also see if any address field has changed and we save it.
     *
     *
     * @param $ids
     */
    private function updateConfirmedFamilyMembersAndAssignToCareAmbassador($ids)
    {
        //per CPM-2256 make sure to update confirmed family member statuses, to be able to pre-fill their data on CA-panel.
        //pre-fill status as well. Enrollee should still come next in queue, since we're not checking for status on Confirmed FamilyMembers Queue
        $attributesToUpdate = [
            'care_ambassador_user_id' => auth()->user()->id,
            'status' => $this->data->get('status') ? Enrollee::getEquivalentToConfirmStatus($this->data->get('status')) : Enrollee::TO_CALL,
            'last_call_outcome' => $this->data->get('reason'),
            'last_call_outcome_reason' => $this->data->get('reason_other'),
            'other_note' => $this->data->get('extra'),
            'preferred_window' => $this->data->get('times') ? createTimeRangeFromEarliestAndLatest($this->data->get('times')) : null,
            'preferred_days' => is_array($this->data->get('days')) ? collect($this->data->get('days'))->reject(function ($d) {
                return 'all' == $d;
            })->implode(', ') : null,
            'primary_phone' => $this->enrollee->primary_phone
        ];
        
        if ($this->shouldUpdateAddress) {
            $attributesToUpdate['address'] = $this->data->get('address');
        }
        
        if ($this->shouldUpdateAddress_2) {
            $attributesToUpdate['address_2'] = $this->data->get('address_2');
        }
        
        Enrollee::whereIn('id', $ids)->update($attributesToUpdate);
    }
    
    private function updateOnConsent()
    {
        
        //If current enrollable address is changed, change for family members
        $this->shouldUpdateAddress = $this->enrollee->address !== $this->data->get('address');
        $this->shouldUpdateAddress_2 = $this->enrollee->address_2 !== $this->data->get('address_2');
        
        //consented
        $this->enrollee->setHomePhoneAttribute($this->data->get('home_phone'));
        $this->enrollee->setCellPhoneAttribute($this->data->get('cell_phone'));
        $this->enrollee->setOtherPhoneAttribute($this->data->get('other_phone'));
        
        //set preferred phone
        switch ($this->data->get('preferred_phone')) {
            case 'home':
                $this->enrollee->setPrimaryPhoneNumberAttribute($this->data->get('home_phone'));
                break;
            case 'cell':
                $this->enrollee->setPrimaryPhoneNumberAttribute($this->data->get('cell_phone'));
                break;
            case 'other':
                $this->enrollee->setPrimaryPhoneNumberAttribute($this->data->get('other_phone'));
                break;
            case 'agent':
                $this->enrollee->setPrimaryPhoneNumberAttribute($this->data->get('agent_phone'));
                $this->enrollee->agent_details = [
                    Enrollee::AGENT_PHONE_KEY => $this->data->get('agent_phone'),
                    Enrollee::AGENT_NAME_KEY => $this->data->get('agent_name'),
                    Enrollee::AGENT_EMAIL_KEY => $this->data->get('agent_email'),
                    Enrollee::AGENT_RELATIONSHIP_KEY => $this->data->get('agent_relationship'),
                ];
                break;
            default:
                $this->enrollee->setPrimaryPhoneNumberAttribute($this->data->get('home_phone'));
        }
        
        $this->enrollee->address = $this->data->get('address');
        $this->enrollee->address_2 = $this->data->get('address_2');
        $this->enrollee->state = $this->data->get('state');
        $this->enrollee->city = $this->data->get('city');
        $this->enrollee->zip = $this->data->get('zip');
        $this->enrollee->email = $this->data->get('email');
        $this->enrollee->dob = $this->data->get('dob');
        $this->enrollee->last_call_outcome = $this->data->get('consented');
        $this->enrollee->care_ambassador_user_id = $this->careAmbassador->user_id;
        
        $this->enrollee->attempt_count = $this->enrollee->attempt_count + 1;
        
        $this->enrollee->other_note = $this->data->get('extra');
        
        if (is_array($this->data->get('days'))) {
            $this->enrollee->preferred_days = collect($this->data->get('days'))->reject(function ($d) {
                return 'all' == $d;
            })->implode(', ');
        }
        
        if (is_array($this->data->get('times'))) {
            $this->enrollee->preferred_window = createTimeRangeFromEarliestAndLatest($this->data->get('times'));
        }
        
        $this->enrollee->status = Enrollee::CONSENTED;
        $this->enrollee->consented_at = Carbon::now()->toDateTimeString();
        $this->enrollee->last_attempt_at = Carbon::now()->toDateTimeString();
    }
    
    private function updateOnRejected()
    {
        $this->enrollee->last_call_outcome = $this->data->get('reason');
        
        if ($this->data->get('reason_other')) {
            $this->enrollee->last_call_outcome_reason = $this->data->get('reason_other');
        }
        
        $this->enrollee->care_ambassador_user_id = $this->careAmbassador->user_id;
        
        $this->enrollee->status = $this->data->get('status');;
        
        $this->enrollee->attempt_count = $this->enrollee->attempt_count + 1;
        $this->enrollee->last_attempt_at = Carbon::now()->toDateTimeString();
    }
    
    private function updateOnUnreachable()
    {
        $this->enrollee->last_call_outcome = $this->data->get('reason');
        
        if ($this->data->get('reason_other')) {
            $this->enrollee->last_call_outcome_reason = $this->data->get('reason_other');
        }
        
        $this->enrollee->other_note = $this->data->get('utc_note');
        
        $this->enrollee->care_ambassador_user_id = $this->careAmbassador->user_id;
        
        if ('requested callback' == $this->data->get('reason')) {
            if ($this->data->has('utc_callback')) {
                $this->enrollee->requested_callback = $this->data->get('utc_callback');
            }
        }
        
        $this->enrollee->status = Enrollee::UNREACHABLE;
        $this->enrollee->attempt_count = $this->enrollee->attempt_count + 1;
        $this->enrollee->last_attempt_at = Carbon::now()->toDateTimeString();
    }
    
    
    private function updateEnrollableModel()
    {
        $status = $this->data->get('status');
        
        if (!$status) {
            //Log Error?
            return false;
        }
        
        if ($status === Enrollee::CONSENTED) {
            $this->updateOnConsent();
        }
        
        if (in_array($status, [Enrollee::SOFT_REJECTED, Enrollee::REJECTED])) {
            $this->updateOnRejected();
        }
        
        if ($status === Enrollee::UNREACHABLE) {
            $this->updateOnUnreachable();
        }
        
        $this->enrollee->save();
    }
    
    private function attachFamilyMembers()
    {
        if (!$this->data->has('confirmed_family_members')) {
            return false;
        }
        
        $ids = $this->data->get('confirmed_family_members');
        
        if (empty($ids)) {
            return false;
        }
        
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        
        $this->updateConfirmedFamilyMembersAndAssignToCareAmbassador($ids);
        
        //make sure to check if duplicate entry exists,
        //also attach the inverse for each on
        $this->enrollee->attachFamilyMembers($ids);
        
        $this->attachInverseRelationship($ids);
    }
    
    private function attachInverseRelationship($ids)
    {
        Enrollee::whereIn('id', $ids)
            ->get()
            ->each(function (Enrollee $e) {
                if (!$e->confirmedFamilyMembers()->where('id', $this->enrollee->id)->exists()) {
                    $e->attachFamilyMembers($this->enrollee->id);
                }
            });
    }
}
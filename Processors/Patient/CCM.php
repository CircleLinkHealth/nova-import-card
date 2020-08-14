<?php


namespace Modules\CcmBilling\Processors\Patient;


use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\Customer\Entities\User;

class CCM implements PatientChargeableServiceProcessor
{
    /**
     * @var User
     */
    private User $patient;
    
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }
    
    public function attach(Carbon $monthYear)
    {
        // TODO: Implement attach() method.
    }
    
    public function fulfill(Carbon $monthYear)
    {
        // TODO: Implement fulfill() method.
    }
    
    public function isAttached(Carbon $monthYear)
    {
        // TODO: Implement isAttached() method.
    }
    
    public function isFulfilled(Carbon $monthYear)
    {
        // TODO: Implement isFulfilled() method.
    }
    
    public function processBilling(Carbon $monthYear)
    {
        // TODO: Implement processBilling() method.
    }
    
    public function shouldAttach(Carbon $monthYear)
    {
        // TODO: Implement shouldAttach() method.
    }
    
    public function shouldFulfill(Carbon $monthYear)
    {
        // TODO: Implement shouldFulfill() method.
    }
}
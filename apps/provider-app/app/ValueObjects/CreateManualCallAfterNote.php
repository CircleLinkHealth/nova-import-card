<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

use App\Algorithms\Calls\NextCallSuggestor\Handlers\SuccessfulCall;
use App\Algorithms\Calls\NextCallSuggestor\Handlers\UnsuccessfulCall;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Arrayable;

class CreateManualCallAfterNote implements Arrayable
{
    private ?User $patient;
    private int $patientId;
    private bool $reached;

    public function __construct(int $patientId, bool $reached)
    {
        $this->patient   = null;
        $this->patientId = $patientId;
        $this->reached   = $reached;
    }

    public static function fromArray(array $arr): CreateManualCallAfterNote
    {
        return new CreateManualCallAfterNote($arr['patientId'], $arr['reached']);
    }

    public static function fromString(string $str): CreateManualCallAfterNote
    {
        $arr = json_decode($str, true);

        return self::fromArray($arr);
    }

    /**
     * @return mixed|string
     */
    public function getCallHandler()
    {
        return $this->reached ? new SuccessfulCall() : new UnsuccessfulCall();
    }

    public function getPatient(): User
    {
        if (is_null($this->patient)) {
            $this->patient = User::without(['roles', 'perms'])->find($this->patientId);
        }

        return $this->patient;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'reached'   => $this->reached,
            'patientId' => $this->patientId,
        ];
    }
}

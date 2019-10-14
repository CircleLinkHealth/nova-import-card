<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\CLH\Repositories\UserRepository;
use App\Search\LocationByName;
use App\Search\RoleByName;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Symfony\Component\HttpFoundation\ParameterBag;
use Validator;

class PracticeStaff implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue
{
    use Importable;

    protected $attributes;

    protected $failedImports = 1;

    protected $modelClass;

    protected $practice;

    protected $resource;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
        $this->practice   = $resource->practice;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }

    public function model(array $row)
    {
        $validator = $this->validateRow($row);

        if ($validator->fails()) {
            return;
        }

        $user = $this->createUser($row);

        if ( ! $user) {
            return;
        }

        $this->attachPhone($user, $row);

        $this->attachEmrDirectAddress($user, $row);

        $this->attachLocation($user, $row);
    }

    public function rules(): array
    {
        return $this->rules;
    }

    private function attachEmrDirectAddress(User $user, array $row)
    {
        if (empty($row['emr_direct_address'])) {
            return;
        }
        $user->setEmrDirectAddressAttribute($row['emr_direct_address']);
    }

    private function attachLocation(User $user, array $row)
    {
        if (empty($row['locations'])) {
            return;
        }

        $locationNames = array_map('trim', explode(',', $row['locations']));

        $locations = [];
        foreach ($locationNames as $locationName) {
            $locations[] = LocationByName::first($locationName);
        }

        $user->attachLocation($locations);
    }

    private function attachPhone(User $user, array $row)
    {
        if (empty($row['phone_number'])) {
            return;
        }

        //get phone type
        $type = collect(PhoneNumber::getTypes())->filter(function ($type) use ($row) {
            return $type == strtolower($row['phone_type']) || starts_with(
                $type,
                strtolower(substr($row['phone_type'], 0, 2))
            );
        })->first();

        $user->phoneNumbers()->create(
            [
                'number'    => (new \App\CLH\Helpers\StringManipulation())->formatPhoneNumber($row['phone_number']),
                'type'      => $type,
                'extension' => $row['phone_extension']
                    ?: null,
            ]
        );
    }

    private function createUser($row)
    {
        $role = RoleByName::first($row['role']);

        if ( ! $role) {
            return null;
        }

        $approveOwn = false;
        if ('provider' == $role->name && ! empty($row['grant_right_to_approve_all_care_plans'])) {
            if (in_array(strtolower($row['grant_right_to_approve_all_care_plans']), ['n', 'no'])) {
                $approveOwn = true;
            }
        }

        $bag = new ParameterBag([
            'email'             => $row['email'],
            'password'          => str_random(),
            'display_name'      => $row['first_name'].' '.$row['last_name'],
            'first_name'        => $row['first_name'],
            'last_name'         => $row['last_name'],
            'username'          => $row['email'],
            'program_id'        => $this->practice->id,
            'is_auto_generated' => true,
            'roles'             => [$role->id],

            //provider
            'approve_own_care_plans' => $approveOwn,
        ]);

        return (new UserRepository())->createNewUser(new User(), $bag);
    }

    private function validateRow($row)
    {
        return Validator::make(
            $row,
            [
                'email'              => 'required|email',
                'first_name'         => 'required',
                'last_name'          => 'required',
                'role'               => 'required',
                'emr_direct_address' => 'nullable|email',
                'phone_number'       => 'nullable|phone:us',
                'phone_extension'    => 'nullable|digits_between:2,4',
            ]
        );
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\CLH\Repositories\UserRepository;
use App\Search\PracticeByName;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Symfony\Component\HttpFoundation\ParameterBag;
use Validator;

class PracticeStaff implements OnEachRow, WithChunkReading, WithValidation, WithHeadingRow
{
    use Importable;

    protected $attributes;

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
        $this->practice   = $this->getPractice();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $row = $row->toArray();

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

    protected function getPractice()
    {
        $fileName = request()->file->getClientOriginalName();

        if ($fileName) {
            $array = explode('.', $fileName);

            $practice = PracticeByName::first($array[0]);

            if ( ! $practice) {
                throw new \Exception(
                    'Practice not found. Please make sure that the file name is a valid Practice name.',
                    500
                );
            }

            return $practice;
        }

        throw new \Exception('Something went wrong. File not found.', 500);
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

        //use scout?
        $locations = Location::where('name', $locationNames)->get();

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
        //get role, use Scout?
        $role = Role::where('display_name', $row['role'])->first();

        if ( ! $role) {
            return null;
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
                'emr_direct_address' => 'sometimes|email',
                'phone_number'       => 'sometimes|phone:us',
                'phone_extension'    => 'sometimes|digits_between:2,4',
            ]
        );
    }
}

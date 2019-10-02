<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\CLH\Repositories\UserRepository;
use App\Search\PracticeByName;
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

    private function attachEmrDirectAddress($user, $row)
    {
        if (empty($row['emr_direct_address'])) {
            return;
        }
    }

    private function attachLocation($user, $row)
    {
        if (empty($row['locations'])) {
            return;
        }
    }

    private function attachPhone($user, $row)
    {
        if (empty($row['phone_number'])) {
            return;
        }
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
                'email'      => 'required|email',
                'first_name' => 'required',
                'last_name'  => 'required',
                'role'       => 'required',
            ]
        );
    }
}

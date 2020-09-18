<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Search\LocationByName;
use App\Search\RoleByName;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use App\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Symfony\Component\HttpFoundation\ParameterBag;

class PracticeStaff extends ReportsErrorsToSlack implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected $fileName;

    protected $modelClass;

    protected $practice;

    protected $repo;

    protected $resource;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
        $this->practice   = $resource->fields->getFieldValue('practice');
        $this->fileName   = $resource->fileName;
        $this->repo       = new UserRepository();
    }

    public static function afterImport(AfterImport $event)
    {
        $importer = $event->getConcernable();

        sendSlackMessage(
            '#background-tasks',
            "Queued job Import Practice Staff for practice {$importer->practice->display_name}, from file {$importer->fileName} is completed.\n"
        );
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function message(): string
    {
        return 'File queued for importing.';
    }

    public function model(array $row)
    {
        if ( ! $this->validateRow($row)) {
            ++$this->rowNumber;

            return;
        }

        try {
            $user = $this->createUser($row);

            if ( ! $user) {
                throw new \Exception('Something went wrong while creating User');
            }

            $this->attachPhone($user, $row);

            $this->attachEmrDirectAddress($user, $row);

            $this->attachLocation($user, $row);
        } catch (\Exception $exception) {
            $this->importingErrors[$this->rowNumber] = $exception->getMessage();
            ++$this->rowNumber;

            return;
        }

        ++$this->rowNumber;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * The message that is displayed before each row error is listed.
     */
    protected function getErrorMessageIntro(): string
    {
        return "The following rows from queued job to import practice staff for practice '{$this->practice->display_name}',
            from file {$this->fileName} failed to import. See reasons below:";
    }

    protected function getImportingRules(): array
    {
        return [
            'email'              => 'required|email',
            'first_name'         => 'required',
            'last_name'          => 'required',
            'role'               => 'required',
            'emr_direct_address' => 'nullable|email',
            'phone_number'       => 'nullable|phone:us',
            'phone_extension'    => 'nullable|digits_between:2,4',
        ];
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
            $location = LocationByName::first($locationName);

            if ( ! $location) {
                throw new \Exception("Location: '{$locationName}' not found. ");
            }
            $locations[] = $location;
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
            return $type == strtolower($row['phone_type']) || Str::startsWith(
                $type,
                strtolower(substr($row['phone_type'], 0, 2))
            );
        })->first();

        $user->phoneNumbers()->create(
            [
                'number'    => (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumber($row['phone_number']),
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
            throw new \Exception("Role: {$row['role']} not found.");
        }

        $approveOwn = false;
        if ('provider' == $role->name && ! empty($row['grant_right_to_approve_all_care_plans'])) {
            if (in_array(strtolower($row['grant_right_to_approve_all_care_plans']), ['n', 'no'])) {
                $approveOwn = true;
            }
        }

        $bag = new ParameterBag([
            'email'             => $row['email'],
            'password'          => Str::random(),
            'display_name'      => $row['first_name'].' '.$row['last_name'],
            'first_name'        => $row['first_name'],
            'last_name'         => $row['last_name'],
            'username'          => $row['email'],
            'suffix'            => $row['clinical_level'],
            'program_id'        => $this->practice->id,
            'is_auto_generated' => true,
            'roles'             => [$role->id],

            //provider
            'approve_own_care_plans' => $approveOwn,
        ]);

        $user = User::whereEmail($row['email'])
            ->ofPractice($this->practice->id)
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['participant', 'administrator']);
            })
            ->where('first_name', $row['first_name'])
            ->where('last_name', $row['last_name'])
            ->first();

        if ( ! $user) {
            return $this->repo->createNewUser($bag);
        }

        return $this->repo->editUser($user, $bag);
    }
}

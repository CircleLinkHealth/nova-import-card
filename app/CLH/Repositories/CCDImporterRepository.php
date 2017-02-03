<?php

namespace App\CLH\Repositories;


use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Role;
use App\User;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDImporterRepository
{
    /**
     * Creates a user with random credentials
     * Used to attach XML CCDs to a Patient
     *
     * @return \App\User
     */
    public function createRandomUser(
        DemographicsImport $demographics,
        ImportedMedicalRecord $imr
    ) {
        $role = Role::whereName('participant')->first();

        if (empty($role)) {
            throw new \Exception('User role not found.', 500);
        }

        $newUserId = str_random(20);

        $email = empty($email = $demographics->email)
            ? $newUserId . '@careplanmanager.com'
            : $email;

        $username = empty($email)
            ? $newUserId
            : $email;

        //user_nicename, display_name
        $user_nicename = empty($fullName = $demographics->first_name . ' ' . $demographics->last_name)
            ? ''
            : ucwords(strtolower($fullName));

        $bag = new ParameterBag([
            'email'             => $email,
            'password'          => str_random(),
            'display_name'      => $user_nicename,
            'first_name'        => $demographics->first_name,
            'last_name'         => $demographics->last_name,
            'username'          => $username,
            'program_id'        => $imr->practice_id,
            'address'           => $demographics->street,
            'address2'          => $demographics->street2,
            'city'              => $demographics->city,
            'state'             => $demographics->state,
            'zip'               => $demographics->zip,
            'is_auto_generated' => true,
            'roles'             => [$role->id],
        ]);

        return (new UserRepository())->createNewUser(new User(), $bag);
    }

    public function toJson($xml)
    {
        $client = new Client([
            'base_uri' => env('CCD_PARSER_BASE_URI'),
        ]);

        $response = $client->request('POST', '/ccda/parse', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body'    => $xml,
        ]);

        if (!$response->getStatusCode() == 200) {
            return [
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ];
        }

        return (string)$response->getBody();
    }
}
<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Repositories;

use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDImporterRepository
{
    /**
     * Creates a user with random credentials
     * Used to attach XML CCDs to a Patient.
     *
     * @return \CircleLinkHealth\Customer\Entities\User
     */
    public function createRandomUser(
        DemographicsImport $demographics,
        ImportedMedicalRecord $imr
    ) {
        $role = Role::whereName('participant')->first();

        if (empty($role)) {
            throw new \Exception('User role not found.', 500);
        }

        $newUserId = Str::random(20);

        $email = empty($email = $demographics->email)
            ? $newUserId.'@careplanmanager.com'
            : $email;

        $username = empty($email)
            ? $newUserId
            : $email;

        //user_nicename, display_name
        $user_nicename = empty($fullName = $demographics->first_name.' '.$demographics->last_name)
            ? ''
            : ucwords(strtolower($fullName));

        $bag = new ParameterBag([
            'email'             => $email,
            'password'          => Str::random(),
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
            'base_uri' => config('services.ccd-parser.base-uri'),
        ]);

        $response = $client->request('POST', '/api/parser', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body'    => $xml,
        ]);

        $responseBody = (string) $response->getBody();

        if ( ! in_array($response->getStatusCode(), [200, 201])) {
            $data = json_encode([
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ]);

            throw new \Exception("Could not process ccd. Data: ${data}");
        }

        return $responseBody;
    }
}

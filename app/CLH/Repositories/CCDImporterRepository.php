<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Repositories;

use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDImporterRepository
{
    /**
     * Creates a user with random credentials
     * Used to attach XML CCDs to a Patient.
     *
     * @param array $params
     *
     * @return User
     * @throws \Exception
     */
    public function createRandomUser(Ccda $ccda, array $params) {
        $role = Role::whereName('participant')->first();

        if (empty($role)) {
            throw new \Exception('User role not found.', 500);
        }

        $newUserId = str_random(25);

        $email = empty($email = $params['email'])
            ? $newUserId.'@careplanmanager.com'
            : $email;

        $username = empty($email)
            ? $newUserId
            : $email;

        //user_nicename, display_name
        $user_nicename = empty($fullName = $params['first_name'].' '.$params['last_name'])
            ? ''
            : ucwords(strtolower($fullName));
        

        $bag = new ParameterBag([
            'email'             => $email,
            'password'          => str_random(),
            'display_name'      => $user_nicename,
            'first_name'        => $params['first_name'],
            'last_name'         => $params['last_name'],
            'username'          => $username,
            'program_id'        => $params['practice_id'],
            'is_auto_generated' => true,
            'roles'             => [$role->id],
            'is_awv'            => Ccda::IMPORTER_AWV === $ccda->source,
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

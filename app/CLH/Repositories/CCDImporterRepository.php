<?php

namespace App\CLH\Repositories;


use App\CLH\Repositories\UserRepository;
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
    public function createRandomUser($blogId, $email = '', $fullName = '')
    {
        $role = Role::whereName('participant')->first();

        if (empty($role)) throw new \Exception('User role not found.', 500);

        $newUserId = str_random(20);

        $user_email = empty($email)
            ? $newUserId . '@careplanmanager.com'
            : $email;

        $user_login = empty($email)
            ? $newUserId
            : $email;

        //user_nicename, display_name
        $user_nicename = empty($fullName)
            ? ''
            : ucwords(strtolower($fullName));

        $bag = new ParameterBag([
            'user_email' => $user_email,
            'user_pass' => 'whatToPutHere',
            'user_nicename' => $user_nicename,
            'display_name' => $user_nicename,
            'user_login' => $user_login,
            'program_id' => $blogId,
            'roles' => [$role->id],
        ]);

        return (new UserRepository())->createNewUser(new User(), $bag);
    }

    public function toJson($xml)
    {
        $client = new Client( [
            'base_uri' => env('CCD_PARSER_BASE_URI'),
        ] );

        $response = $client->request( 'POST', '/ccda/parse', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body' => $xml,
        ] );

        if ( !$response->getStatusCode() == 200 ) {
            return [$response->getStatusCode(), $response->getReasonPhrase()];
        }

        return (string) $response->getBody();
    }
}
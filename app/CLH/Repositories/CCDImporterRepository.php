<?php

namespace App\CLH\Repositories;


use App\CLH\CCD\ImportedItems\DemographicsImport;
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
    public function createRandomUser(DemographicsImport $demographics)
    {
        $role = Role::whereName('participant')->first();

        if (empty($role)) throw new \Exception('User role not found.', 500);

        $newUserId = str_random(20);

        $user_email = empty($email = $demographics->email)
            ? $newUserId . '@careplanmanager.com'
            : $email;

        $user_login = empty($email)
            ? $newUserId
            : $email;

        //user_nicename, display_name
        $user_nicename = empty($fullName = $demographics->first_name . ' ' . $demographics->last_name)
            ? ''
            : ucwords(strtolower($fullName));

        $user = User::create([
            'user_email' => $user_email,
            'user_pass' => str_random(),
            'user_nicename' => $user_nicename,
            'display_name' => $user_nicename,
            'first_name' => $demographics->first_name,
            'last_name' => $demographics->last_name,
            'user_login' => $user_login,
            'program_id' => $demographics->program_id,
            'address' => $demographics->street,
            'address2' => $demographics->street2,
            'city' => $demographics->city,
            'state' => $demographics->state,
            'zip' => $demographics->zip,
            'is_auto_generated' => true,
        ]);

        $user->attachRole($role->id);

        (new UserRepository())->createDefaultCarePlan($user, null);


//        $bag = new ParameterBag([
//            'user_email' => $user_email,
//            'user_pass' => str_random(),
//            'user_nicename' => $user_nicename,
//            'display_name' => $user_nicename,
//            'first_name' => $demographics->first_name,
//            'last_name' => $demographics->last_name,
//            'user_login' => $user_login,
//            'program_id' => $demographics->program_id,
//            'address' => $demographics->street,
//            'address2' => $demographics->street2,
//            'city' => $demographics->city,
//            'state' => $demographics->state,
//            'zip' => $demographics->zip,
//            'is_auto_generated' => true,
//            'roles' => [$role->id],
//        ]);
        return $user;
//        return (new UserRepository())->createNewUser(new User(), $bag);
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
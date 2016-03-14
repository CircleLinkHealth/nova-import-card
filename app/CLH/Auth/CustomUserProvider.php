<?php
namespace App\CLH\Auth;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\User;
use MikeMcLin\WpPassword\Facades\WpPassword;

class CustomUserProvider implements UserProvider {

    protected $model;

    public function __construct(UserContract $model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {
        $user = User::find($identifier);
        //dd($user);
        //$user =  $this->dummyUser();
        $this->user = $user;
        return $user;
    }

    public function retrieveByToken($identifier, $token)
    {
    }

    public function updateRememberToken(UserContract $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = User::query();

        foreach ($credentials as $key => $value)
        {
            // hack for column names
            if($key == 'email') {
                $key = 'user_email';
            }
            if ( ! str_contains($key, 'password') && ! str_contains($key, 'user_pass'))
            {
                $query->where($key, $value);
            }
        }

        $query = User::query();
        $query->where('user_login', $credentials['email']);
        //$query->orWhere('user_email', $credentials['email']);
        $query->orWhereRaw('LOWER(user_email) = ?', [$credentials['email']])->get();
        $user = $query->first();
        return $user;
    }
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $password_hashed = $user->getAuthPassword();

        if( isset($credentials['password']) ) {
            $plain_password = $credentials['password'];
        } else if( isset($credentials['user_pass']) ) {
            $plain_password = $credentials['user_pass'];
        } else {
            return response('Password not provided', 422);
        }

        if ( (strtolower($user->user_email) != strtolower($credentials['email'])) && (strtolower($user->user_login) != strtolower($credentials['email'])) ) {
            return false;
        }

        /*
         * Migrating Passwords from WP
         *
         * If the User has a WP password, a new hash will be created using Laravel's method
         * and it will be saved in the password field.
         */

        //Get rid of this when we phase WP out.
        //echo PHP_EOL.'<br>plain = ' . $plain_password;
        //echo PHP_EOL.'<br>hashed = ' . $password_hashed;
       // echo PHP_EOL.'<br>check result = '. WpPassword::check($plain_password, $password_hashed);
        if(WpPassword::check($plain_password, $password_hashed)) {
            $user->password = \Hash::make($plain_password);
            $user->save();
            //dd('passed wp check, updated');
            return true;
        }
        //dd('failed wp check, done');

        if (\Hash::check($plain_password, $user->password)) {
            return true;
        }

        return false;
    }

    protected function dummyUser()
    {
        $attributes = array(
            'id' => 330,
            'remember_token' => "",
            'username' => 'chuckles@test.com',
            'password' => \Hash::make('SuperSecret'),
            'name' => 'Dummy User',
        );
        return new GenericUser($attributes);
    }
}
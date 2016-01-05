<?php
namespace App\Auth;

use Hautelook\Phpass\PasswordHash;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\User;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        /*
         * Migrating Passwords from WP
         *
         * If the User has a WP password, a new hash will be created using Laravel's method
         * and it will be saved in the password field.
         */
        if(WpPassword::check($plain_password, $password_hashed)) {

            $hash = \Hash::make($plain_password);
            $user->user_pass = $hash;
            $user->password = $hash;
            $user->save();
            return true;
        } else {
            if ($user->user_email == $credentials['email']) {
                if (\Hash::check($plain_password, $user->password)) {
                    return true;
                }
            }
            return false;
        }
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
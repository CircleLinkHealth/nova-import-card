<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Validator;

//use App\CLH\Traits\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{

    use AuthenticatesAndRegistersUsers;


    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard $auth
     * @param  \Illuminate\Contracts\Auth\Registrar $registrar
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest', ['except' => 'getLogout']);





//        if (auth()->check())
//        {
//            auth()->logout();
//            session()->flush();
//            return redirect()->route('login', [])->send();
//        }

    }


    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        if (\Auth::check()) {
            return view('auth.register');
        } else {
            return redirect()->back();
        }

    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        auth()->logout();
        session()->flush();
        return redirect()->route('login', [])->send();
    }


    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        if (\Auth::check()) {
            $validator = $this->registrar->validator($request->all());

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            auth()->login($this->registrar->create($request->all()));

            return redirect($this->redirectPath());
        } else {
            return redirect()->back();
        }
    }

    public function redirectPath()
    {
        if (auth()->user()) {
            if (auth()->user()->hasRole('administrator')) {
                return '/admin';
            } else if (auth()->user()->hasRole('provider')) {
                return '/manage-patients/dashboard';
            } else if (auth()->user()->hasRole('care-center')) {
                return '/manage-patients/dashboard';
            } else {
                return '/manage-patients/dashboard';
            }
        }
        /*
        $role = auth()->user()->roles[0]->name;

        switch ($role)
        {
            case 'administrator': return '/admin';
            case 'manager': return '/manage-patients/dashboard';
            case 'participant': return '/manage-patients/dashboard';
            case 'provider': return '/manage-patients/dashboard';
            default: return '/manage-patients/dashboard';
        }
        */
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function authenticated(
        Request $request,
        User $user
    ) {


        return redirect()->intended($this->redirectPath());
    }
}

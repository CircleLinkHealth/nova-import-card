<?php namespace App\Http\Controllers\Auth;

use App\AppConfig;
use App\Http\Controllers\Controller;
use App\Models\PatientSession;
use App\User;
use DateTime;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Validator;

//use App\CLH\Traits\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{

    use AuthenticatesAndRegistersUsers;

    protected $username = 'user_email';

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


        //Check whether to authenticate using user_email or password
        if ($request->has('user_email'))
        {
            if (! str_contains($request->input('user_email'), '@'))
            {
                $this->username = 'user_login';

                $request->merge([
                    'user_login' => $request->input('user_email')
                ]);
            }
        }


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
        // run AppConfig app_config processes here
        $appConfigs = AppConfig::all();

        // cur_month_ccm_time_last_reset
        $curMonthCcmTimeLastReset = $appConfigs->where('config_key', 'cur_month_ccm_time_last_reset')->first();
        if($curMonthCcmTimeLastReset) {
            $curMonthStartDate = (new DateTime('first day of this month'))->format('Y-m-d');
            $curMonthStartDate = $curMonthStartDate . ' 00:00:01';
            if($curMonthCcmTimeLastReset->config_value < $curMonthStartDate) {
                // reset all users time
                $users = User::whereHas('patientInfo', function ($q) {
                    $q->where('cur_month_activity_time', '>', '0');
                })->get();

                foreach($users as $user) {
                    $user->patientInfo()->update(array("cur_month_activity_time" => "0"));
                }

                // update config timestamp
                $curMonthCcmTimeLastReset->config_value = date('Y-m-d H:i:s');
                $curMonthCcmTimeLastReset->save();
            }
        }

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

        //CLEAR OUT ANY REMAINING PATIENT SESSIONS ON LOGIN
        $session = PatientSession::where('user_id', '=', $user->ID)
            ->delete();

        return redirect()->intended($this->redirectPath());
    }
}

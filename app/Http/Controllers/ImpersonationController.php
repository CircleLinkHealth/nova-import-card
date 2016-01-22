<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\Request;

class ImpersonationController extends Controller {

    public function postImpersonate(Request $request)
    {
        $email = $request->input('email');

        try {
            $user = User::whereUserEmail($email)->firstOrFail();
        } catch (\Exception $e) {
            echo "No User was found with email address $email <br><br> Please go back and try again.";
            exit;
        }

        auth()->setUserToImpersonate($user);

        return redirect()->route('patients.dashboard', [$email]);
    }

}

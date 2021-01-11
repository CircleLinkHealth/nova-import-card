<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function showHomepage() {
        if (auth()->guest()) {
            return 'lkjlkjlkj';
//            return redirect()->route('login');
        }
    }

    public function selfEnrollmentNova()
    {
        if (auth()->user()->isAdmin()){
            return 'to nova';
        }
    }
}

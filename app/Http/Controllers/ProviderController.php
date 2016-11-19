<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProviderController extends Controller
{

    public function store(Request $request){

        if($request->ajax()){

            return $request->input();

        }

    }

}

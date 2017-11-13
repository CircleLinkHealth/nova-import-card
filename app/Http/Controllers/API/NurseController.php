<?php

namespace App\Http\Controllers\API;

use App\Filters\NurseFilters;
use App\Nurse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NurseController extends Controller
{
    public function index(NurseFilters $filters) {
        return Nurse::filter($filters)->get();
    }
}

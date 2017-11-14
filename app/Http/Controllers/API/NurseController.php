<?php

namespace App\Http\Controllers\API;

use App\Filters\NurseFilters;
use App\Http\Resources\NurseInfo;
use App\Nurse;
use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;

class NurseController extends ApiController
{
    /**    
     *   @SWG\GET(
     *     path="nurses",
     *     tags={"nurses"},
     *     summary="Get Nurses Info",
     *     description="Display a listing of nurses",
     *     @SWG\Response(
     *         response="default", 
     *         description="A listing of nurses"
     *     )
     *   )   
     * @return \Illuminate\Http\Response
     */
    public function index(NurseFilters $filters) {
        return NurseInfo::collection(Nurse::filter($filters)->get());
    }
}

<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Http\Requests\ImportPracticeStaffCsvNovaRequest;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

//we cache routes on prod. I don't know if this affects nova
Route::get(
    '/import-staff-to-practice',
    function (ImportPracticeStaffCsvNovaRequest $request) {
        $resource = $request->newResource();
        
        //get the practice from the textbox
        //getting the first one for demo purposes
        $request->setPractice(Practice::first());
        
        //redirect to controller action somehow
        
        return redirect()->action('Sparclex\NovaImportCard\ImportController@handle', ['request' => $request]);
    }
);

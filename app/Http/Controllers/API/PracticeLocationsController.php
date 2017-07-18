<?php

namespace App\Http\Controllers\API;

use App\CLH\Facades\StringManipulation;
use App\Practice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PracticeLocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($primaryPracticeId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $existingLocations = $primaryPractice->locations->map(function ($loc) use (
            $primaryPractice
        ) {

            $contactType = $loc->clinicalEmergencyContact->first()->pivot->name ?? null;
            $contactUser = $loc->clinicalEmergencyContact->first() ?? null;

            return [
                'id'                        => $loc->id,
                'clinical_contact'          => [
                    'email'     => $contactUser->email ?? null,
                    'first_name' => $contactUser->first_name ?? null,
                    'last_name'  => $contactUser->last_name ?? null,
                    'type'      => $contactType ?? 'billing_provider',
                ],
                'timezone'                  => $loc->timezone ?? 'America/New_York',
                'ehr_password'              => $loc->ehr_password,
                'city'                      => $loc->city,
                'address_line_1'            => $loc->address_line_1,
                'address_line_2'            => $loc->address_line_2,
                'ehr_login'                 => $loc->ehr_login,
                'errorCount'                => 0,
                'isComplete'                => true,
                'name'                      => $loc->name,
                'postal_code'               => $loc->postal_code,
                'state'                     => $loc->state,
                'validated'                 => true,
                'phone'                     => StringManipulation::formatPhoneNumber($loc->phone),
                'fax'                       => StringManipulation::formatPhoneNumber($loc->fax),
                'emr_direct_address'        => $loc->emr_direct_address,
                'sameClinicalIssuesContact' => $primaryPractice->same_clinical_contact,
                'sameEHRLogin'              => $primaryPractice->same_ehr_login,
            ];
        });

        return response()->json($existingLocations);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

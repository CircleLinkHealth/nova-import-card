<?php

namespace App\Http\Controllers\API;

use App\CarePerson;
use App\CLH\Facades\StringManipulation;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePracticeLocation;
use App\Location;
use App\Practice;
use App\User;
use Illuminate\Http\Request;

class PracticeLocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($primaryPracticeId)
    {
        $primaryPractice = Practice::select(['id'])->whereId($primaryPracticeId)->first();

        $existingLocations = $primaryPractice->locations->map(function ($loc) use (
            $primaryPractice
        ) {

            $contactType = $loc->clinicalEmergencyContact->first()->pivot->name ?? null;
            $contactUser = $loc->clinicalEmergencyContact->first() ?? null;

            return [
                'id'                        => $loc->id,
                'clinical_contact'          => [
                    'email'      => $contactUser->email ?? null,
                    'first_name' => $contactUser->first_name ?? null,
                    'last_name'  => $contactUser->last_name ?? null,
                    'type'       => $contactType ?? 'billing_provider',
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
                'practice'                  => $primaryPractice,
                'practice_id'               => $primaryPractice->id,
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
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePracticeLocation $request, $primaryPracticeId, $locationId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $formData = $request->input();

        $sameClinicalContact = $request->input('sameClinicalIssuesContact');
        $sameEHRLogin = $request->input('sameEHRLogin');

        $location = Location::updateOrCreate([
            'id' => $formData['id'],
        ], [
            'practice_id'    => $primaryPractice['id'],
            'name'           => $formData['name'],
            'phone'          => StringManipulation::formatPhoneNumberE164($formData['phone']),
            'fax'            => StringManipulation::formatPhoneNumberE164($formData['fax']),
            'address_line_1' => $formData['address_line_1'],
            'address_line_2' => $formData['address_line_2'] ?? null,
            'city'           => $formData['city'],
            'state'          => $formData['state'],
            'timezone'       => $formData['timezone'],
            'postal_code'    => $formData['postal_code'],
            'ehr_login'      => $sameEHRLogin
                ? $request->input('locations')[0]['ehr_login']
                : $formData['ehr_login'] ?? null,
            'ehr_password'   => $sameEHRLogin
                ? $request->input('locations')[0]['ehr_password']
                : $formData['ehr_password'] ?? null,
        ]);


        $location->emr_direct_address = $formData['emr_direct_address'];
        $primaryPractice->same_clinical_contact = false;

        //If clinical contact is same for all, then get the data from the first location.
        if ($sameClinicalContact) {
            $formData['clinical_contact']['type'] = $request->input('locations')[0]['clinical_contact']['type'];
            $formData['clinical_contact']['email'] = $request->input('locations')[0]['clinical_contact']['email'];
            $formData['clinical_contact']['firstName'] = $request->input('locations')[0]['clinical_contact']['firstName'];
            $formData['clinical_contact']['lastName'] = $request->input('locations')[0]['clinical_contact']['lastName'];

            $primaryPractice->same_clinical_contact = true;
        }

        $primaryPractice->same_ehr_login = false;

        if ($sameEHRLogin) {
            $primaryPractice->same_ehr_login = true;
        }

        $primaryPractice->save();

        if ($formData['clinical_contact']['type'] == CarePerson::BILLING_PROVIDER) {
            //clean up other contacts, just in case this was just set as the billing provider
            $location->clinicalEmergencyContact()->sync([]);
        } else {
            $clinicalContactUser = User::whereEmail($formData['clinical_contact']['email'])
                ->first();

            if (!$clinicalContactUser) {
                $clinicalContactUser = User::create([
                    'program_id' => $primaryPractice->id,
                    'email'      => $formData['clinical_contact']['email'],
                    'first_name' => $formData['clinical_contact']['first_name'],
                    'last_name'  => $formData['clinical_contact']['last_name'],
                    'password'   => 'password_not_set',
                ]);

                $clinicalContactUser->attachPractice($primaryPractice);
                $clinicalContactUser->attachLocation($location);

                //clean up other contacts before adding the new one
                $location->clinicalEmergencyContact()->sync([]);

                $location->clinicalEmergencyContact()->attach($clinicalContactUser->id, [
                    'name' => $formData['clinical_contact']['type'],
                ]);
            }
        }

        if ($primaryPractice->lead) {
            $primaryPractice->lead->attachLocation($location);
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

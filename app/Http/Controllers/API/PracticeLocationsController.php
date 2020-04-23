<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePracticeLocation;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class PracticeLocationsController extends Controller
{
    /**
     * Remove the specified resource from storage.
     *
     * @param int   $id
     * @param mixed $practiceId
     * @param mixed $locationId
     * @param mixed $primaryPracticeId
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($primaryPracticeId, $locationId)
    {
        $loc = Location::wherePracticeId($primaryPracticeId)->find($locationId);

        if ($loc) {
            $loc->delete();
        }

        return response()->json($loc);
    }

    /**
     * Display a listing of the resource.
     *
     * @param mixed $primaryPracticeId
     *
     * @return \Illuminate\Http\Response
     */
    public function index($primaryPracticeId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $existingLocations = $primaryPractice->locations->sortBy('name')->values()->map(function ($loc) use (
            $primaryPractice
        ) {
            return $this->present($loc, $primaryPractice);
        });

        return response()->json($existingLocations);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     * @param mixed                    $primaryPracticeId
     * @param mixed                    $locationId
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePracticeLocation $request, $primaryPracticeId, $locationId)
    {
        $primaryPractice = Practice::find($primaryPracticeId);

        $formData = $request->input();

        $sameClinicalContact = $request->input('sameClinicalIssuesContact');
        $sameEHRLogin        = $request->input('sameEHRLogin');

        $location = Location::updateOrCreate([
            'id' => $formData['id'],
        ], [
            'practice_id'               => $primaryPractice['id'],
            'name'                      => $formData['name'],
            'phone'                     => (new StringManipulation())->formatPhoneNumberE164($formData['phone']),
            'clinical_escalation_phone' => (new StringManipulation())->formatPhoneNumberE164($formData['clinical_escalation_phone']),
            'fax'                       => (new StringManipulation())->formatPhoneNumberE164($formData['fax']),
            'address_line_1'            => $formData['address_line_1'],
            'address_line_2'            => $formData['address_line_2'] ?? null,
            'city'                      => $formData['city'],
            'state'                     => $formData['state'],
            'timezone'                  => $formData['timezone'],
            'postal_code'               => $formData['postal_code'],
            'ehr_login'                 => $formData['ehr_login'] ?? null,
            'ehr_password'              => $formData['ehr_password'] ?? null,
        ]);

        if (1 == Location::where('practice_id', $primaryPractice->id)->count()) {
            $location->is_primary = 1;
            $location->save();
        }

        $location->emr_direct_address = $formData['emr_direct_address'];

        //handle ehr login credentials
        $primaryPractice->same_ehr_login = false;

        if ($sameEHRLogin) {
            $primaryPractice->same_ehr_login = true;

            $primaryPractice->locations->map(function ($loc) use ($formData) {
                $loc->ehr_login = $formData['ehr_login'] ?? null;
                $loc->ehr_password = $formData['ehr_password'] ?? null;
                $loc->save();
            });
        }

        //handle clinical contact
        $this->handleClinicalContact($formData['clinical_contact'], $primaryPractice, $location);

        $primaryPractice->same_clinical_contact = false;

        if ($sameClinicalContact) {
            $primaryPractice->same_clinical_contact = true;

            $primaryPractice->locations->map(function ($loc) use ($formData, $primaryPractice) {
                $this->handleClinicalContact($formData['clinical_contact'], $primaryPractice, $loc);
            });
        }

        $primaryPractice->save();

        if ($primaryPractice->lead) {
            $primaryPractice->lead->attachLocation($location);
        }

        return response()->json($this->present($location, $primaryPractice));
    }

    private function handleClinicalContact(array $clinicalContact, Practice $primaryPractice, Location $location)
    {
        //clean up other contacts
        $location->clinicalEmergencyContact()->sync([]);

        if (CarePerson::BILLING_PROVIDER == $clinicalContact['type']) {
            return;
        }

        $clinicalContactUser = User::whereEmail($clinicalContact['email'])->first();

        if ( ! $clinicalContactUser) {
            $clinicalContactUser = User::create([
                'program_id' => $primaryPractice->id,
                'email'      => $clinicalContact['email'],
                'first_name' => $clinicalContact['first_name'],
                'last_name'  => $clinicalContact['last_name'],
                'password'   => 'password_not_set',
            ]);
        }

        $clinicalContactUser->attachPractice($primaryPractice, []);
        $clinicalContactUser->attachLocation($location);

        //clean up other contacts before adding the new one
        $location->clinicalEmergencyContact()->sync([]);

        $location->clinicalEmergencyContact()->attach($clinicalContactUser->id, [
            'name' => $clinicalContact['type'],
        ]);
    }

    private function present(Location $loc, Practice $primaryPractice)
    {
        $contactType = $loc->clinicalEmergencyContact->first()->pivot->name ?? null;
        $contactUser = $loc->clinicalEmergencyContact->first() ?? null;

        return [
            'id'               => $loc->id,
            'clinical_contact' => [
                'email'      => optional($contactUser)->email ?? null,
                'first_name' => optional($contactUser)->getFirstName() ?? null,
                'last_name'  => optional($contactUser)->getLastName() ?? null,
                'type'       => $contactType ?? 'billing_provider',
            ],
            'timezone'                  => $loc->timezone ?? 'America/New_York',
            'ehr_password'              => $loc->ehr_password,
            'city'                      => $loc->city,
            'address_line_1'            => $loc->address_line_1,
            'address_line_2'            => $loc->address_line_2,
            'ehr_login'                 => $loc->ehr_login,
            'name'                      => $loc->name,
            'postal_code'               => $loc->postal_code,
            'state'                     => $loc->state,
            'validated'                 => true,
            'phone'                     => (new StringManipulation())->formatPhoneNumber($loc->phone),
            'clinical_escalation_phone' => (new StringManipulation())->formatPhoneNumber($loc->clinical_escalation_phone),
            'fax'                       => (new StringManipulation())->formatPhoneNumber($loc->fax),
            'emr_direct_address'        => $loc->emr_direct_address,
            'sameClinicalIssuesContact' => $primaryPractice->same_clinical_contact,
            'sameEHRLogin'              => $primaryPractice->same_ehr_login,
            'practice'                  => $primaryPractice,
            'practice_id'               => $primaryPractice->id,
        ];
    }
}

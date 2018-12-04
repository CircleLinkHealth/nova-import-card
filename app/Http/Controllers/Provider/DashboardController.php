<?php

namespace App\Http\Controllers\Provider;

use App\ChargeableService;
use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePracticeSettingsAndNotifications;
use App\Http\Resources\SAAS\PracticeChargeableServices;
use App\Location;
use App\Practice;
use App\PracticeEnrollmentTips;
use App\SafeRequest;
use App\Services\OnboardingService;
use App\Settings;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $invites;
    protected $locations;
    protected $practices;
    protected $users;
    protected $onboardingService;
    protected $primaryPractice;

    public function __construct(
        InviteRepository $inviteRepository,
        LocationRepository $locationRepository,
        PracticeRepository $practiceRepository,
        UserRepository $userRepository,
        OnboardingService $onboardingService,
        Request $request
    ) {
        $this->invites           = $inviteRepository;
        $this->locations         = $locationRepository;
        $this->practices         = $practiceRepository;
        $this->users             = $userRepository;
        $this->onboardingService = $onboardingService;

        $this->practiceSlug = $request->route('practiceSlug');

        $this->primaryPractice = Practice::whereName($this->practiceSlug)->first();

        //return these with all requests
        $this->returnWithAll = [
            'practice' => $this->primaryPractice,
        ];
    }

    public function getCreateLocation()
    {
        $primaryPractice = $this->primaryPractice;

        if (! $primaryPractice) {
            return response('Practice not found', 404);
        }

        return view('provider.location.create', [
            'practiceSlug' => $this->practiceSlug,
            'practice'     => $this->primaryPractice,
        ]);
    }

    public function getCreatePractice()
    {
        $users     = $this->onboardingService->getExistingStaff($this->primaryPractice);
        $locations = $this->onboardingService->getExistingLocations($this->primaryPractice);

        return view('provider.practice.create', array_merge([
            'practiceSlug' => $this->practiceSlug,
            'staff'        => $users['existingUsers'],
            'locations'    => $locations,
        ], $this->returnWithAll));
    }

    public function getCreateNotifications()
    {
        $invoiceRecipients = $this->primaryPractice->getInvoiceRecipients()->pluck('email')->implode(',');

        return view('provider.notifications.create', array_merge([
            'practice'          => $this->primaryPractice,
            'practiceSlug'      => $this->practiceSlug,
            'practiceSettings'  => $this->primaryPractice->cpmSettings(),
            'invoiceRecipients' => $invoiceRecipients,
        ], $this->returnWithAll));
    }

    public function getCreateChargeableServices()
    {
        $practiceChargeableRel = $this->primaryPractice->chargeableServices;

        $allChargeableServices = ChargeableService::all()
                                                  ->map(function ($service) use ($practiceChargeableRel) {
                                                      $existing = $practiceChargeableRel
                                                          ->where('id', '=', $service->id)
                                                          ->first();

                                                      $service->is_on = false;

                                                      if ($existing) {
                                                          $service->amount = $existing->pivot->amount;
                                                          $service->is_on  = true;
                                                      }

                                                      return $service;
                                                  });

        return view('provider.chargableServices.create', array_merge([
            'practice'           => $this->primaryPractice,
            'practiceSlug'       => $this->practiceSlug,
            'practiceSettings'   => $this->primaryPractice->cpmSettings(),
            'chargeableServices' => PracticeChargeableServices::collection($allChargeableServices),
        ], $this->returnWithAll));
    }

    public function getCreateStaff()
    {
        $practice = $this->primaryPractice->load('settings');

        if (! $practice) {
            return response('Practice not found', 404);
        }

        $practiceSlug = $this->practiceSlug;

        return view('provider.user.create-staff', compact('invite', 'practiceSlug', 'practice'));
    }

    public function getCreateEnrollment()
    {
        return view('provider.enrollment.create', array_merge([
            'practice'         => $this->primaryPractice,
            'practiceSlug'     => $this->practiceSlug,
            'practiceSettings' => $this->primaryPractice->cpmSettings(),
            'tips'             => optional($this->primaryPractice->enrollmentTips)->content,
        ], $this->returnWithAll));
    }

    public function getIndex()
    {
        return view('provider.layouts.dashboard', array_merge([
            'practiceSlug' => $this->practiceSlug,
        ], $this->returnWithAll));
    }

    public function postStoreInvite(Request $request)
    {
        $invite = $this->invites->create([
            'inviter_id' => auth()->user()->id,
            'role_id'    => $request->input('role'),
            'email'      => $request->input('email'),
            'subject'    => $request->input('subject'),
            'message'    => $request->input('message'),
            'code'       => str_random(20),
        ]);
    }

    public function postStoreLocations(Request $request)
    {
        $primaryPractice = $this->primaryPractice;

        $result = $this->onboardingService->postStoreLocations($primaryPractice, $request);

        return get_class($result) == JsonResponse::class
            ? $result
            : response()->json([
                'message' => "{$primaryPractice->display_name}'s Locations were successfully updated.",
            ]);
    }

    public function postStoreNotifications(UpdatePracticeSettingsAndNotifications $request)
    {
        $settingsInput = $request->input('settings');
        $errors        = collect();

        if (isset($settingsInput['dm_audit_reports'])) {
            $locationsWithoutDM = collect();

            foreach ($this->primaryPractice->locations as $location) {
                if (! $location->emr_direct_address) {
                    $locationsWithoutDM->push($location);
                }
            }

            if ($this->primaryPractice->locations->count() == $locationsWithoutDM->count()) {
                unset($settingsInput['dm_audit_reports']);
                $errors->push('Send Audit Reports via Direct Mail was not activated because none of the Locations have a DM address. Please add a Direct Address for at least one Location, and then try activating the Notification again.');
            } elseif ($locationsWithoutDM->count() == 0) {
                //
            } else {
                $locs = implode(', ', $locationsWithoutDM->pluck('name')->all());

                $errors->push("Locations: <strong>$locs</strong> are missing a <strong>Direct Address</strong>. Click Locations (left) to correct that.");
            }
        }

        if (isset($settingsInput['efax_audit_reports'])) {
            $locationsWithoutFax = collect();

            foreach ($this->primaryPractice->locations as $location) {
                if (! $location->fax) {
                    $locationsWithoutFax->push($location);
                }
            }

            if ($this->primaryPractice->locations->count() == $locationsWithoutFax->count()) {
                unset($settingsInput['efax_audit_reports']);
                $errors->push('Send Audit Reports via eFax was not activated because none of the Locations have a fax number. Please add a Fax Number for at least one Location, and then try activating the Notification again.');
            } elseif ($locationsWithoutFax->count() == 0) {
                //
            } else {
                $locs = implode(', ', $locationsWithoutFax->pluck('name')->all());

                $errors->push("Locations: <strong>$locs</strong> are missing a <strong>Fax Number</strong>. Go to the Locations (left) to correct that.");
            }
        }

        if (! isset($settingsInput['api_auto_pull'])) {
            $settingsInput['api_auto_pull'] = 0;
        }

        if (empty($settingsInput['note_font_size'])) {
            unset($settingsInput['note_font_size']);
        }

        $this->primaryPractice->syncSettings(new Settings($settingsInput ?? []));

        $invoiceRecipients      = $request->input('invoice_recipients');
        $weeklyReportRecipients = $request->input('weekly_report_recipients');

        $this->primaryPractice->update([
            'invoice_recipients'       => $invoiceRecipients,
            'weekly_report_recipients' => $weeklyReportRecipients,
        ]);

        return redirect()->back()->withErrors($errors);
    }

    public function postStoreChargeableServices(Request $request)
    {
        $services = $request['chargeable_services'];

        $sync = [];

        foreach ($services as $id => $service) {
            if (array_key_exists('is_on', $service)) {
                $sync[$id] = [
                    'amount' => $service['amount'],
                ];
            }
        }

        $this->primaryPractice
            ->chargeableServices()
            ->sync($sync);

        return redirect()->back();
    }

    public function postStoreStaff(Request $request)
    {
        $primaryPractice = $this->primaryPractice;

        $this->onboardingService->postStoreStaff($primaryPractice, $request);

        return response()->json([
            'message' => "{$primaryPractice->display_name}'s Staff were successfully updated.",
        ]);
    }

    public function postStorePractice(Request $request)
    {
        $update['federal_tax_id'] = $request->input('federal_tax_id');

        if ($request->input('lead_id')) {
            $update['user_id'] = $request->input('lead_id');
        }

        if (auth()->user()->isAdmin()) {
            $update['bill_to_name'] = $request->input('bill_to_name');
            $update['clh_pppm']     = $request->input('clh_pppm');
            $update['term_days']    = $request->input('term_days');
            $update['active']       = $request->input('is_active');

            if (! ! $this->primaryPractice->active && ! ! ! $update['active']) {
                $enrolledPatientsExist = User::ofPractice($this->primaryPractice->id)
                                             ->ofType('participant')
                                             ->whereHas('patientInfo', function ($q) {
                                                 $q->enrolled();
                                             })
                                             ->exists();

                if ($enrolledPatientsExist) {
                    return redirect()
                        ->back()
                        ->withErrors([
                            'is_active' => "The practice cannot be deactivated because it has enrolled patients.",
                        ]);
                }
            }
        }

        $this->primaryPractice->update($update);

        if ($request->has('primary_location')) {
            Location::whereId($request['primary_location'])
                    ->update([
                        'is_primary' => true,
                    ]);
        }

        return redirect()
            ->back()
            ->with('message', "The practice has been updated successfully.");
    }

    public function postStoreEnrollment(SafeRequest $request)
    {
        //Summernote is vulnerable to XSS, so we remove entirely the special chars
        //Laravel already sanitizes suspicious characters and can result to something like this:
        //<p>all good</p>&lt;script&rt;alert('hi')&lt;script&gt;
        //Also, Laravel does not handle this: <a href="javascript:alert('hi')">Click me. I am safe!</a>
        $detail = $request->input('tips');
        $detail = $this->removeEncodedSpecialChars($detail);
        $detail = $this->removeSuspiciousJsCode($detail);
        PracticeEnrollmentTips::updateOrCreate([ 'practice_id' => $this->primaryPractice->id ], [ 'content' => $detail ]);

        return redirect()
            ->back()
            ->with('message', "Enrollment tips were saved successfully.");
    }

    private function removeEncodedSpecialChars($str)
    {
        /**
        & (ampersand) becomes &amp;
        " (double quote) becomes &quot;
        ' (single quote) becomes &#039;
        < (less than) becomes &lt;
        > (greater than) becomes &gt;
         */
        $pattern = ['/&amp;/', '/&quot;/', '/&#039;/', '/&lt;/', '/&gt;/'];
        return preg_replace($pattern, '', $str);
    }

    private function removeSuspiciousJsCode($str)
    {
        return preg_replace('/javascript:/', '', $str);
    }
}

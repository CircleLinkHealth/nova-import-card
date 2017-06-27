<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Practice;
use App\Services\OnboardingService;
use App\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $invites;
    protected $locations;
    protected $practices;
    protected $users;
    protected $onboardingService;

    public function __construct(
        InviteRepository $inviteRepository,
        LocationRepository $locationRepository,
        PracticeRepository $practiceRepository,
        UserRepository $userRepository,
        OnboardingService $onboardingService,
        Request $request
    ) {
        parent::__construct($request);

        $this->invites = $inviteRepository;
        $this->locations = $locationRepository;
        $this->practices = $practiceRepository;
        $this->users = $userRepository;
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

        if (!$primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingLocations($primaryPractice);

        return view('provider.location.create', array_merge([
            'leadId'       => auth()->user()->id,
            'practiceSlug' => $this->practiceSlug,
        ], $this->returnWithAll));
    }

    public function getCreatePractice()
    {
        $users = $this->onboardingService->getExistingStaff($this->primaryPractice);

        return view('provider.practice.create', array_merge([
            'practiceSlug'     => $this->practiceSlug,
            'staff'            => $users['existingUsers'],
        ], $this->returnWithAll));
    }

    public function getCreateNotifications()
    {
        if ($this->primaryPractice->settings->isEmpty()) {
            $practiceSettings = $this->primaryPractice->syncSettings(new Settings());
        }

        return view('provider.notifications.create', array_merge([
            'practiceSlug'     => $this->practiceSlug,
            'practiceSettings' => $practiceSettings ?? $this->primaryPractice->settings->first(),
        ], $this->returnWithAll));
    }

    public function getCreateStaff()
    {
        $primaryPractice = $this->primaryPractice;

        if (!$primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingStaff($primaryPractice);

        $practiceSlug = $this->practiceSlug;

        return view('provider.user.create-staff', array_merge(compact('invite', 'practiceSlug'), $this->returnWithAll));
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

    public function postStoreNotifications(Request $request)
    {
        $this->primaryPractice->syncSettings(new Settings($request->input('settings') ?? []));

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

        $this->primaryPractice->update($update);

        return redirect()->back();
    }
}

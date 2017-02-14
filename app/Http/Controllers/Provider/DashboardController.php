<?php

namespace App\Http\Controllers\Provider;

use App\Contracts\Repositories\InviteRepository;
use App\Contracts\Repositories\LocationRepository;
use App\Contracts\Repositories\PracticeRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Practice;
use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Prettus\Validator\Exceptions\ValidatorException;

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

        $this->practiceSlug = Route::current()->getParameter('practiceSlug');

        $this->primaryPractice = Practice::whereName($this->practiceSlug)->first();
    }

    public function getCreateLocation()
    {
        $primaryPractice = auth()->user()->primaryPractice;

        if (!$primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingLocations($primaryPractice);

        return view('provider.location.create', [
            'leadId'       => auth()->user()->id,
            'practiceSlug' => $this->practiceSlug,
        ]);
    }

    public function getCreatePractice()
    {
        $practice = $this->practices->firstOrNew([
            'user_id' => auth()->user()->id,
        ]);

        return view('provider.practice.create', [
            'practiceSlug' => $this->practiceSlug,
            'practice'     => $practice,
        ]);
    }

    public function getCreateStaff()
    {
        $primaryPractice = $this->primaryPractice;

        if (!$primaryPractice) {
            return response('Practice not found', 404);
        }

        $this->onboardingService->getExistingStaff($primaryPractice);

        $practiceSlug = $this->practiceSlug;

        return view('provider.user.create-staff', compact('invite', 'practiceSlug'));
    }

    public function getIndex()
    {
        return view('provider.layouts.dashboard');
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

        $this->onboardingService->postStoreLocations($primaryPractice, $request);

        return response()->json([
            'message' => "{$primaryPractice->display_name}'s Locations were successfully updated.",
        ]);
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
        $input = $request->input();

        try {
            $program = $this->practices->create([
                'name'         => str_slug($input['name']),
                'user_id'      => auth()->user()->id,
                'display_name' => $input['name'],
                'description'  => $input['description'],
            ]);
        } catch (ValidatorException $e) {
            return redirect()
                ->back()
                ->withErrors($e->getMessageBag()->getMessages())
                ->withInput();
        }

        return redirect()->back();
    }
}

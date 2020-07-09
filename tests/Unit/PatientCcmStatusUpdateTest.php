<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

class PatientCcmStatusUpdateTest extends TestCase
{
    use UserHelpers;
    use WithoutMiddleware;
    protected $admin;
    protected $nurse;
    protected $patient;

    protected $practice;

    protected $requestClass = Request::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->practice = factory(Practice::class)->create();
        $this->patient  = $this->createUser($this->practice->id, 'participant');
        $this->admin    = $this->createUser($this->practice->id, 'administrator');

        $this->nurse = $this->createUser($this->practice->id, 'care-center');
    }

    /**
     * Trying to make Safe Request Work.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param string $content
     *
     * @return \Illuminate\Testing\TestResponse
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $kernel = $this->app->make(HttpKernel::class);

        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));

        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content
        );

        $request = $this->requestClass::createFromBase($symfonyRequest);

        $response = $kernel->handle(
            $request
        );

        if ($this->followRedirects) {
            $response = $this->followRedirects($response);
        }

        $kernel->terminate($request, $response);

        return $this->createTestResponse($response);
    }

    /**
     * Conditions for withdrawn 1st call are:
     * Patient->inboundCall()->where('status', 'reached') <= 1.
     * However for the notes page, count should be 0 and note has successful call.
     *
     * @return void
     */
    public function test_withdrawn_1st_call_status_is_saved_from_notes_page()
    {
        //todo: currently failing because of safe request not containing any data when passing through the controller. Normal request works
//        auth()->login($this->admin);
//        $this->requestClass = SafeRequest::class;
//        $response = $this->actingAs($this->admin)->call('POST',
//            route('patient.note.store', ['patientId' => $this->patient->id]), $this->getNoteStoreParams());
//
//        $response->assertStatus(302);
//
//        $info = $this->patient->patientInfo()->first();
//        $this->assertEquals($info->ccm_status, 'withdrawn_1st_call');
    }

    public function test_withdrawn_1st_call_status_is_saved_from_user_repository()
    {
        $roles  = (array) Role::whereName('participant')->firstOrFail()->id;
        $params = new ParameterBag([
            'program_id'       => $this->practice->id,
            'roles'            => $roles,
            'withdrawn_reason' => 'Changed Insurance',
            'ccm_status'       => Patient::WITHDRAWN,
        ]);

        $userRepo = new UserRepository();
        $userRepo->editUser($this->patient, $params);

        $info = $this->patient->patientInfo()->first();
        $this->assertEquals($info->ccm_status, Patient::WITHDRAWN_1ST_CALL);
        $this->assertEquals($info->withdrawn_reason, 'Changed Insurance');
    }

    /*public function test_withdrawn_1st_call_status_is_saved_on_mass_withdrawal()
    {
        auth()->login($this->admin);

        $response = $this->actingAs($this->admin)->call(
            'GET',
            route('admin.users.doAction'),
            [
                'action'           => 'withdraw',
                'withdrawn-reason' => 'No Longer Interested',
                'users'            => [
                    $this->patient->id,
                ],
            ]
        );

        $info = $this->patient->patientInfo()->first();
        $this->assertEquals($info->ccm_status, 'withdrawn_1st_call');
        $this->assertEquals($info->withdrawn_reason, 'No Longer Interested');
    }*/

    private function getNoteStoreParams(): array
    {
        return [
            'ccm_status'             => Patient::WITHDRAWN,
            'withdrawn_reason'       => 'Changed Insurance',
            'withdrawn_Reason_other' => '',
            'general_comment'        => '',
            'type'                   => 'CCM Welcome Call',
            'performed_at'           => '2019-11-23T16:47',
            'phone'                  => 'outbound',
            'welcome_call'           => 'welcome_call',
            'tcm'                    => 'hospital',
            'summary'                => '',
            'body'                   => 'Test Body',
            'patient_id'             => $this->patient->id,
            'logger_id'              => optional(auth()->user())->id,
            'author_id'              => optional(auth()->user())->id,
            'programId'              => $this->practice->id,
            'task_status'            => '',
        ];
    }
}

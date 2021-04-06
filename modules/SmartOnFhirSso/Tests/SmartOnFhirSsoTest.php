<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Tests;

use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\SamlSp\Console\RegisterSamlUserMapping;
use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use CircleLinkHealth\SmartOnFhirSso\Http\Controllers\EpicSsoController;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\MetadataResponse;
use CircleLinkHealth\SmartOnFhirSso\Http\Requests\OAuthResponse;
use CircleLinkHealth\SmartOnFhirSso\Services\SsoService;
use CircleLinkHealth\SmartOnFhirSso\ValueObjects\IdTokenDecoded;
use CircleLinkHealth\SmartOnFhirSso\ValueObjects\SsoIntegrationSettings;
use Mockery;

class SmartOnFhirSsoTest extends CustomerTestCase
{
    const EHR_NAME    = 'epic';
    const EHR_PATIENT = 'patient_fhir_id_testing';
    const EHR_USER    = 'testUser';

    protected function setUp(): void
    {
        parent::setUp();

        Ehr::updateOrCreate([
            'name' => self::EHR_NAME,
        ], [
            'pdf_report_handler' => '',
        ]);

        \Artisan::call(RegisterSamlUserMapping::class, [
            'cpmUserId' => $this->superadmin()->id,
            'idp'       => self::EHR_NAME,
            'idpUserId' => self::EHR_USER,
        ]);

        $ehrId = Ehr::firstWhere('name', self::EHR_NAME)->id;

        TargetPatient::updateOrCreate([
            'user_id' => $this->patient()->id,
        ], [
            'ehr_id'         => $ehrId,
            'ehr_patient_id' => self::EHR_PATIENT,
            'practice_id'    => 1,
            'department_id'  => 1,
            'description'    => 'test',
        ]);
    }

    public function test_it_authenticates_user_from_smart_request()
    {
        $this->setUpMocks();

        $route    = route('smart.on.fhir.sso.launch');
        $response = $this->get($route.'?iss=epic&launch=token');
        $response->assertRedirect();

        $route    = route('smart.on.fhir.sso.epic.code');
        $response = $this->get($route.'?code=code');
        $response->assertRedirect();

        self::assertAuthenticatedAs($this->superadmin());
    }

    public function test_it_authenticates_user_from_smart_request_and_redirects_to_target_url()
    {
        $this->setUpMocks();

        $route    = route('smart.on.fhir.sso.launch');
        $response = $this->get($route.'?iss=epic&launch=token');
        $response->assertRedirect();

        $route    = route('smart.on.fhir.sso.epic.code');
        $response = $this->get($route.'?code=code');

        $targetUrl = route('patient.note.index', ['patientId' => $this->patient()->id]);
        $response->assertRedirect($targetUrl);

        self::assertAuthenticatedAs($this->superadmin());
    }

    private function setUpMocks()
    {
        $repo = Mockery::mock(SsoService::class)->makePartial();
        $this->instance(SsoService::class, $repo);

        $repo->shouldReceive('getMetadataEndpoints')
            ->andReturn(new MetadataResponse([
                'rest' => [
                    [
                        'security' => [
                            'extension' => [
                                [
                                    'extension' => [
                                        [
                                            'url'      => 'authorize',
                                            'valueUri' => 'authorize_testing',
                                        ],
                                        [
                                            'url'      => 'token',
                                            'valueUri' => 'token_testing',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]));

        $epicController = app(EpicSsoController::class);
        $repo->shouldReceive('getSettings')
            ->andReturn(new SsoIntegrationSettings($epicController->getPlatform(), $epicController->getUserIdPropertyName(), $epicController->getClientId(), $epicController->getRedirectUrl()));

        $repo->shouldReceive('getPlatform')
            ->andReturn($epicController->getPlatform());

        $repo->shouldReceive('getOAuthToken')
            ->andReturn(new OAuthResponse([
                'id_token' => 'id_token',
                'patient'  => self::EHR_PATIENT,
            ]));

        $repo->shouldReceive('decodeIdToken')
            ->andReturn(new IdTokenDecoded([
                ['typ' => '', 'alg' => ''],
                ['fhirUser' => '', 'iss' => '', 'aud' => '', 'sub' => self::EHR_USER, 'iat' => 0, 'exp' => 0],
                'a_random_string',
            ]));
    }
}

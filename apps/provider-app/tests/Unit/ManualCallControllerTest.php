<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Algorithms\Calls\NextCallSuggestor\Handlers\SuccessfulCall;
use App\Algorithms\Calls\NextCallSuggestor\Suggestion;
use App\Algorithms\Calls\NextCallSuggestor\Suggestor;
use App\Contracts\CallHandler;
use App\Http\Controllers\ManualCallController;
use App\ValueObjects\CreateManualCallAfterNote;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ManualCallControllerTest extends CustomerTestCase
{
    use WithoutMiddleware;

    private int $userIdIncrement = 0;

    public function test_it_fails_if_message_is_not_the_correct_instance()
    {
        $this->putMessageInSession('some string to make it fail');

        $response = $this->actingAs($this->fakeNurse())
            ->get(route('manual.call.create', ['patientId' => $this->fakePatient()->id]));

        $response->assertStatus(500);

        $response->withException(new \DomainException('Type Error in ManualCallController `$message` should be an instance of.'));
    }

    public function test_it_fails_validation_if_the_key_does_not_exist_in_cache()
    {
        $response = $this->actingAs($this->fakeNurse())
            ->get(route('manual.call.create', ['patientId' => $this->fakePatient()->id]));

        $response->assertStatus(403);
    }

    /*
     * works when running with `./vendor/phpunit/phpunit/phpunit --filter=ManualCallController`
     * but not with `composer test`
    public function test_it_passes_correct_variables_to_view()
    {
        [$nurse, $patient] = $this->mockSuggestor($handler = new SuccessfulCall());
        $patient->save();

        $this->putMessageInSession(json_encode((new CreateManualCallAfterNote($patient->id, true))->toArray()));

        $response = $this->actingAs($nurse)
            ->get(route('manual.call.create', ['patientId' => $patient->id]));

        $response->assertViewIs('wpUsers.patient.calls.create');
        $response->assertViewHasAll(array_merge([
            'ccm_above' => false,
            'patient'   => $patient,
            'messages'  => ['Successfully Created Note!'],
        ], (new Suggestion())->toArray()));
    }
    */

    private function fakeNurse()
    {
        return factory(User::class)->make([
            'id'           => $this->getUserId(),
            'display_name' => 'Soulla Masoulla',
            'program_id'   => 8,
        ]);
    }

    private function fakePatient()
    {
        return factory(User::class)->make([
            'id'         => $this->getUserId(),
            'program_id' => 8,
        ]);
    }

    private function getUserId()
    {
        if (0 === $this->userIdIncrement) {
            $this->userIdIncrement = User::orderBy('id', 'desc')->first()->id;
        }

        return ++$this->userIdIncrement;
    }

    private function mockSuggestor(CallHandler $handler)
    {
        $patient = $this->fakePatient();
        $nurse   = $this->fakeNurse();

        $this->mock(PatientServicesForTimeTracker::class, function ($m) {
            $m->shouldReceive('get')
                ->andReturn(new PatientChargeableSummaryCollection(collect()));
        });

        $this->mock(Suggestor::class, function ($m) use ($patient, $handler) {
            $m->shouldReceive('handle')
                // ->with($patient, $handler)
                ->once()
                ->andReturn(new Suggestion());
        });

        return [$nurse, $patient];
    }

    private function putMessageInSession($message)
    {
        session()->put(ManualCallController::SESSION_KEY, $message);
    }
}

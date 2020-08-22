<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\CustomerTestCase;
use Tests\Feature\UserScope\Assertions\Assertion;

abstract class TestCase extends CustomerTestCase
{
    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    private ?User $actor;
    private array $callArgs;
    private $content;
    private array $cookies;
    private array $files;
    private string $method;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    private \Illuminate\Database\Eloquent\Collection $newLocations;
    private array $parameters;
    private array $server;
    private string $uri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient(2);

        collect($this->newLocations = factory(Location::class, 2)->create([
            'practice_id' => $this->practice()->id,
        ]))->each(function ($location) {
            $this->resetPatient();
            $patients = collect($this->patient(3));

            Patient::whereIn('user_id', $patients->pluck('id')->all())->update([
                'preferred_contact_location' => $location->id,
            ]);

            $patients->each(function ($patient) use ($location) {
                $patient->locations()->sync([$location->id]);

                CarePlan::where('user_id', $patient->id)
                    ->update([
                        'status' => CarePlan::PROVIDER_APPROVED,
                    ]);
            });
        });
    }

    public function assert(Assertion ...$assertions)
    {
        $responseData = $this->extractResponseData($response = $this->sendRequest());

        collect($assertions)->each(function (Assertion $assertion) use ($responseData) {
            $collection = collect($responseData->get($assertion->lookIn));

            if ($collection->isEmpty()) {
                throw new \Exception('No data found in response');
            }
            call_user_func(\Closure::fromCallable([$this, 'assert'.class_basename($assertion)]), $this->actor, $collection, $assertion->key);
        });

        $this->reset();
    }

    public function assertCallback(callable $cb)
    {
        call_user_func($cb, $this->sendRequest(), $this->actor);

        $this->reset();
    }

    public function assertLocation(User $actor, Collection $collection, ?string $key = null)
    {
        $actorLocations = $actor->locations->pluck('id')->all();

        if ( ! $key) {
            $this->assertTrue($collection->reject(function ($locationId) use ($actorLocations) {
                return in_array($locationId, $actorLocations);
            })->isEmpty(), 'The response contains location IDs the actor does not have.');
        }

        $candidates = $collection->pluck($key)->filter();

        if ($candidates->isEmpty()) {
            throw new \Exception("`$key` not found in response data");
        }

        $this->assertTrue($collection->whereNotIn($key, $actor->locations->pluck('id')->all())->isEmpty(), 'The response contains patients from other Locations.');
    }

    public function assertPractice(User $actor, Collection $collection, ?string $key = null)
    {
        $actorPractices = $actor->practices->pluck('id')->all();

        if ( ! $key) {
            $this->assertTrue($collection->reject(function ($practiceId) use ($actorPractices) {
                return in_array($practiceId, $actorPractices);
            })->isEmpty(), 'The response contains practice IDs the actor does not have.');
        }

        $candidates = $collection->pluck($key)->filter();

        if ($candidates->isEmpty()) {
            throw new \Exception("`$key` not found in response data");
        }

        $this->assertTrue($collection->whereNotIn($key, $actorPractices)->isEmpty(), 'The response contains patients from other Practices.');
    }

    public function calling($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): self
    {
        $this->content    = $content;
        $this->cookies    = $cookies;
        $this->files      = $files;
        $this->method     = $method;
        $this->parameters = $parameters;
        $this->server     = $server;
        $this->uri        = $uri;

        return $this;
    }

    public function withLocationScope(): self
    {
        $this->actor = $this->provider();

        $this->actor->scope = User::SCOPE_LOCATION;
        $this->actor->save();

        $detached = $this->actor->locations()->detach($this->newLocations->first()->id);
        $this->actor->load('locations');

        $this->assertEquals(1, $detached);

        return $this;
    }

    public function withPracticeScope(): self
    {
        $this->actor = $this->provider();

        return $this;
    }

    protected function extractResponseData(TestResponse $response): Collection
    {
        $responseData = null;

        if (is_json($response->getContent())) {
            $responseData = $response->decodeResponseJson();
        }

        if ( ! $responseData) {
            $responseData = $response->original->gatherData();
        }

        if (empty($responseData)) {
            throw new \Exception('No response data found');
        }

        return collect($responseData);
    }

    private function reset()
    {
        $this->callArgs = [];
        $this->actor    = null;
    }

    private function sendRequest(): TestResponse
    {
        $response = $this->actingAs($this->actor)
            ->call($this->method, $this->uri, $this->parameters, $this->cookies, $this->files, $this->server, $this->content);

        $response->assertOk();

        return $response;
    }
}

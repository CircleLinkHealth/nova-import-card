<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\UserScope\Assertions\Assertion;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
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
    private array $parameters;
    private array $server;
    private string $uri;

    public function actor(): ?User
    {
        return $this->actor;
    }

    public function assert(Assertion ...$assertions)
    {
        $responseData = $this->extractResponseData($response = $this->sendRequest());

        collect($assertions)->each(function (Assertion $assertion) use ($responseData) {
            $collection = collect($responseData->get($assertion->lookIn));

            if ($collection->isEmpty()) {
                throw new \Exception('No data found in response');
            }
            call_user_func(\Closure::fromCallable([$this, 'assert'.class_basename($assertion)]), $this->actor, $collection, $assertion->key, $assertion->billingProviderId);
        });

        $this->reset();
    }

    public function assertCallback(callable $cb)
    {
        call_user_func($cb, $this->sendRequest(), $this->actor);

        $this->reset();
    }

    public function assertLocation(User $actor, Collection $collection, string $locationIdKey, ?string $billingProviderIdKey)
    {
        $candidates = $collection->pluck($locationIdKey)->filter()->values();

        if ($candidates->isEmpty()) {
            throw new \Exception("`$locationIdKey` not found in response data");
        }

        $this->assertTrue($collection->whereNotIn($locationIdKey, $actor->locations->pluck('id')->all())->isEmpty(), 'The response contains patients from other Locations.');

        if (true === (bool) $actor->providerInfo->approve_own_care_plans) {
            if ( ! $billingProviderIdKey) {
                throw new \Exception('$billingProviderIdKey is null');
            }
            $this->assertTrue($collection->where($billingProviderIdKey, '!=', $actor->id)->isEmpty(), 'The response contains other Billing providers\' patients.');
        }
    }

    public function assertPractice(User $actor, Collection $collection, string $key)
    {
        $candidates = $collection->pluck($key)->filter();

        if ($candidates->isEmpty()) {
            throw new \Exception("`$key` not found in response data");
        }

        $this->assertTrue($collection->whereNotIn($key, $actor->practices->pluck('id')->all())->isEmpty(), 'The response contains patients from other Practices.');
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

    public function resetActor(): void
    {
        $this->actor = null;
    }

    public function withLocationScope(): self
    {
        $this->actor = User::whereFirstName(\UserScopeTestsSeeder::PROVIDER_WITH_LOCATION_3_SCOPE_FIRST_NAME)
            ->whereLastName(\UserScopeTestsSeeder::PROVIDER_WITH_LOCATION_3_SCOPE_LAST_NAME)
            ->with(['practices', 'locations'])
            ->first();

        if (is_null($this->actor)) {
            throw new \Exception('Please run `php artisan db:seed --class=UserScopeTestsSeeder`');
        }

        return $this;
    }

    public function withPracticeScope(): self
    {
        $this->actor = User::whereFirstName(\UserScopeTestsSeeder::PROVIDER_WITH_PRACTICE_SCOPE_FIRST_NAME)
            ->whereLastName(\UserScopeTestsSeeder::PROVIDER_WITH_PRACTICE_SCOPE_LAST_NAME)
            ->with(['practices', 'locations'])
            ->first();

        if (is_null($this->actor)) {
            throw new \Exception('Please run `php artisan db:seed --class=UserScopeTestsSeeder`');
        }

        return $this;
    }

    protected function extractResponseData(TestResponse $response): Collection
    {
        $responseData = null;

        if (is_json($response->getContent())) {
            $responseData = $response->decodeResponseJson();
        }

        if ( ! $responseData) {
            $responseData = $response->original->getData();
        }

        if (empty($responseData)) {
            throw new \Exception('No response data found');
        }

        return collect($responseData);
    }

    private function reset()
    {
        $this->callArgs = [];
    }

    private function sendRequest(): TestResponse
    {
        $response = $this->actingAs($this->actor)
            ->call($this->method, $this->uri, $this->parameters, $this->cookies, $this->files, $this->server, $this->content);

        $this->assertTrue(in_array($response->status(), [200, 302]), 'It seems like the request failed');

        return $response;
    }
}

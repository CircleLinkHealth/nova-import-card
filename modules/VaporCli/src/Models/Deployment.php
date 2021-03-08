<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Models;

use Illuminate\Support\Str;

class Deployment
{
    /**
     * The deployment data.
     *
     * @var array
     */
    public $deployment;

    /**
     * Create a new model instance.
     *
     *
     * @return void
     */
    public function __construct(array $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Get an item from the deployment data.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->deployment[$key];
    }

    /**
     * Get the names of the displayable steps.
     *
     *
     * @return array
     */
    public function displayableSteps(array $displayedSteps = [])
    {
        return collect($this->steps)
            ->filter(function ($step) {
                return 'pending' !== $step['status'] &&
                           'cancelled' !== $step['status'];
            })->map(function ($step) {
                return $this->formatDeploymentStepName($step['name']);
            })->filter(function ($step) use ($displayedSteps) {
                return ! in_array($step, $displayedSteps);
            })->all();
    }

    /**
     * Determine if the deployment has ended.
     *
     * @return bool
     */
    public function hasEnded()
    {
        return $this->has_ended;
    }

    /**
     * Determine if the deployment has any failed deployment hooks.
     *
     * @return bool
     */
    public function hasFailedHooks()
    {
        return (collect($this->steps)->first(function ($step) {
            return 'RunDeploymentHooks' == $step['name'];
        })['status'] ?? null) === 'failed';
    }

    /**
     * Determine if the deployment has target domains.
     *
     * @return bool
     */
    public function hasTargetDomains()
    {
        return isset($this->deployment['target_domains']) &&
               ! empty($this->deployment['target_domains']);
    }

    /**
     * Determine if the deployment is finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return 'finished' == $this->status;
    }

    /**
     * Convert the model into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->deployment;
    }

    /**
     * Get the vanity domain for the deployment environment.
     *
     * @return string
     */
    public function vanityDomain()
    {
        return $this->deployment['environment']['vanity_domain'];
    }

    /**
     * Format the deployment step name into a displayable value.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatDeploymentStepName($name)
    {
        return str_replace(
            ['Iam', 'Api', 'Dns', 'Ensure', 'Update', 'Run'],
            ['IAM', 'API', 'DNS', 'Ensuring', 'Updating', 'Running'],
            ucwords(Str::snake($name, ' '))
        );
    }

    /**
     * Determine if the given deployment step should be displayed.
     *
     *
     * @return bool
     */
    protected function stepShouldBeDisplayed(array $step)
    {
        return 'pending' !== $step['status'] &&
               ! in_array($step['name'], $this->displayedSteps);
    }
}

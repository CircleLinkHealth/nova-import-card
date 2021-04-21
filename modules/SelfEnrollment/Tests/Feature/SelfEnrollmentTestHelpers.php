<?php


namespace CircleLinkHealth\SelfEnrollment\Tests\Feature;


use CircleLinkHealth\SelfEnrollment\Console\Commands\PrepareDataForReEnrollmentTestSeeder;

trait SelfEnrollmentTestHelpers
{
    /**
     * @var
     */
    private $factory;

    public function createEnrollees(int $number = 1, array $arguments = [])
    {
        if (1 === $number) {
            return $this->factory()->createEnrollee($this->practice(), $this->provider(), $arguments);
        }

        $coll = collect();

        for ($i = 0; $i < $number; ++$i) {
            $coll->push($this->factory()->createEnrollee($this->practice(), $this->provider()));
        }

        return $coll;
    }

    private function factory()
    {
        if (is_null($this->factory)) {
            $this->factory = $this->app->make(PrepareDataForReEnrollmentTestSeeder::class);
        }

        return $this->factory;
    }
}
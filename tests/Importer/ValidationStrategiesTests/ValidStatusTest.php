<?php

class ValidStatusTest extends TestCase
{
    public function test_null_status_returns_inactive()
    {
        $this->assertFalse($this->getValidationStrategy()->isValid($this->mockProblem(null)));
    }

    public function getValidationStrategy()
    {
        return new \App\Importer\Section\Validators\ValidStatus();
    }

    public function mockProblem($status)
    {
        $problem = new stdClass();
        $problem->status = $status;

        return $problem;
    }

    public function test_active_status_returns_active()
    {
        $this->assertTrue($this->getValidationStrategy()->isValid($this->mockProblem('active')));
    }

    public function test_chronic_status_returns_active()
    {
        $this->assertTrue($this->getValidationStrategy()->isValid($this->mockProblem('chronic')));
    }

    public function test_empty_string_status_returns_inactive()
    {
        $this->assertFalse($this->getValidationStrategy()->isValid($this->mockProblem('')));
    }

    public function test_not_object_returns_inactive()
    {
        $this->assertFalse($this->getValidationStrategy()->isValid('not an object'));
    }
}

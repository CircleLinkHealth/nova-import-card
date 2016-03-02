<?php

class ValidStatusTest extends TestCase
{
    public function getValidationStrategy()
    {
        return new \App\CLH\CCD\Importer\ValidationStrategies\ValidStatus();
    }

    public function mockProblem($status)
    {
        $problem = new stdClass();
        $problem->status = $status;

        return $problem;
    }

    public function test_null_status_returns_inactive()
    {
        $this->assertFalse( $this->getValidationStrategy()->validate( $this->mockProblem( null ) ) );
    }

    public function test_active_status_returns_active()
    {
        $this->assertTrue( $this->getValidationStrategy()->validate( $this->mockProblem( 'active' ) ) );
    }

    public function test_chronic_status_returns_active()
    {
        $this->assertTrue( $this->getValidationStrategy()->validate( $this->mockProblem( 'chronic' ) ) );
    }

    public function test_empty_string_status_returns_inactive()
    {
        $this->assertFalse( $this->getValidationStrategy()->validate( $this->mockProblem( '' ) ) );
    }

    public function test_not_object_returns_inactive()
    {
        $this->assertFalse( $this->getValidationStrategy()->validate( 'not an object' ) );
    }
}
<?php

class DashboardTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testDashboardGet()
    {
        $this->visit(route('get.provider.dashboard'));
    }
}

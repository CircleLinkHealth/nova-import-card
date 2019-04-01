<?php


namespace App\Services;


class ProviderReportService
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function formatReportDataForView()
    {

    }

}
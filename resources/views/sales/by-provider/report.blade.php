@extends('sales.partials.report-layout')

<?php

$enrollmentSection = \App\Reports\Sales\Provider\Sections\EnrollmentSummary::class;
$rangeSection = \App\Reports\Sales\Provider\Sections\RangeSummary::class;
$financialSection = \App\Reports\Sales\Provider\Sections\FinancialSummary::class;
$practiceSection = \App\Reports\Sales\Provider\Sections\PracticeDemographics::class;

?>

@section('content')

    @if(array_key_exists($rangeSection, $data))

        @include('sales.partials.overall-section', ['data' => $data])

    @endif

    @if(array_key_exists($enrollmentSection, $data))

        @include('sales.partials.enrollement-section', ['data' => $data])

    @endif

    @if(array_key_exists($financialSection, $data))

        @include('sales.partials.financial-section', ['data' => $data])


    @endif

    @if(array_key_exists($practiceSection, $data))

        @include('sales.partials.practice-section', ['data' => $data])

    @endif

@stop

@extends('partials.providerUI')

<?php
    $today = \Carbon\Carbon::now()->toFormattedDateString();
?>

@section('title', 'Care Plan Assessment')
@section('activity', 'Care Plan Assessment')

@push('styles')
    <style>
        .top-20 {
            margin-top: 20px;
        }

        .color-blue {
            color: #109ace;
        }

        .bg-blue {
            background-color: #109ace;
        }

        input[type='radio'] {
            display: inline;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <section class="patient-summary">
            <div class="patient-info__main">
                <div class="row">
                    <div class="col-xs-12 top-20">
                        <h1 class="color-blue">G0506 Template Form for MD</h1>
                    </div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12">
                                <h4>Risk level for your patient</h4>
                                <div class="form-group">
                                    <div>
                                        <label>
                                            <input type="radio" name="risk" value="high"> High
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" name="risk" value="medium"> Medium
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" name="risk" value="low"> Low
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" name="risk" value="vulnerable"> Vulnerable
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
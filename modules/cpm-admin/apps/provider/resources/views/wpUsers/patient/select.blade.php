@extends('partials.providerUI')

@section('title', 'Patient Search')
@section('activity', 'Patient Search')

@section('content')

    <div class="container">
        <section class="main-form">
            <div class="row">
                <div class="main-form-container col-lg-8 col-lg-offset-2 main-form-container-last"
                     style="border-bottom: 3px solid #50b2e2; padding-bottom: 100px">
                    <div class="row">
                        <div class="main-form-title">
                            Select a Patient
                        </div>
                    </div>
                    <div class="form-item form-item-spacing form-item--first col-sm-12 col-lg-12"
                         style="text-align: center">
                        @include('partials.search')
                    </div>
                </div>
            </div>

        </section>
    </div>



@stop
@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', 'Patient Listing')

@section('content')
    @push('styles')
        <style>
            .overflow-scroll {
                overflow: scroll;
            }

            .VueTables__table.table {
                width: 1500px;
            }

            #patient-list-table > div.table-responsive > table > thead {
                background-color: #d2e3ef !important;
            }

            .page-link  {
                cursor: pointer !important;
                border: 1px solid #3498db !important;
                color: #fff !important;
                background: #3498db !important;
                border-bottom: 1px solid #2386c8 !important;
                margin-bottom: 5px !important;
            }
        </style>
    @endpush
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient List
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 overflow-scroll">
                    <patient-list :show-provider-patients-button="@json(auth()->user()->isProvider() && auth()->user()->scope !== App\User::SCOPE_LOCATION)"
                                  :is-admin="@json(auth()->user()->isAdmin())"
                                  ref="patientList">
                    </patient-list>
                </div>
            </div>
        </div>
    </div>
@endsection

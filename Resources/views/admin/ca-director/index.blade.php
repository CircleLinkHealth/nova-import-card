@extends(! Auth::guest() && Cerberus::hasPermission('admin-access') ? 'cpm-admin::partials.adminUI' : 'partials.providerUI')

@section('title', 'Care Ambassador Director Palen')
@section('activity', 'Care Ambassador Director Palen')

@push('styles')
    <style>
        .cpm-editable {
            color: #000;
        }

        .highlight {
            color: green;
            font-weight: bold;
        }

        td.details-control {
            color: #fff;
            background: url('{{ asset('/vendor/datatables-images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url('{{ asset('/vendor/datatables-images/details_close.png') }}') no-repeat center center;
        }

        div.modal-dialog {
            z-index: 1051; /* should ensure the modal body is always visible */
        }

        div.main-section {
            width: 94%;
            margin-left: 3%;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 main-section">
                <div class="row">
                    <div class="col-sm-12" style="text-align: center">
                        <h1>Care Ambassador Director Panel</h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div>
                            <div class="panel-body">
                                <div>
                                    @include('core::partials.errors.errors')
                                    @include('core::partials.errors.messages')
                                </div>
                                @if (!auth()->user()->isCallbacksAdmin())
                                    <div class="col-md-12" style="text-align: right">
                                        <a class="btn btn-danger btn-m"
                                           href="{{route('ca-director.test-enrollees', ['erase' => true, 'redirect' => true])}}">Erase
                                            Demo Patients</a>
                                        <a class="btn btn-info btn-m"
                                           href="{{route('ca-director.test-enrollees', ['redirect' => true])}}">Create Demo
                                            Patients</a>
                                    </div>
                                @endif
                                <div>
                                    <ca-director-panel ref="CaDirectorPanel" auth-role="{{auth()->user()->practiceOrGlobalRole()->name}}"></ca-director-panel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <link rel="stylesheet" href="https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css">
    </div>
@endsection

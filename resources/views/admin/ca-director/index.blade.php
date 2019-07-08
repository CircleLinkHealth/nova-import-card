@extends('partials.adminUI')

@push('styles')
    <style>
        .cpm-editable {
            color:#000;
        }

        .highlight {
            color:green;
            font-weight:bold;
        }
        td.details-control {
            color:#fff;
            background: url('{{ mix('/vendor/datatables-images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url('{{ mix('/vendor/datatables-images/details_close.png') }}') no-repeat center center;
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
                    <div class="col-sm-12 panel-body">
                        <form action="{{route('ca-director.upload-csv')}}" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <div class="col-sm-12">
                                <input type="file" name="source_file" required>
                            </div>
                            <div class="col-sm-12">
                                <input type="submit" class="btn btn-md btn-default" value="Upload file">
                            </div>

                        </form>
                    </div>
                    <div class="col-sm-12">
                        <div>
                            <div class="panel-body">
                                <div>
                                    @include('errors.errors')
                                    @include('errors.messages')
                                </div>
                                <div>
                                   <ca-director-panel ref="CaDirectorPanel"></ca-director-panel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
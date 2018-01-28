@extends('partials.providerUI')

@section('title', 'Create Internal User')

@section('content')

    @push('scripts')
    <script>
        $(document).ready(function () {
            let practices = $(".practices")

            practices.select2({closeOnSelect:false})

            //show selections in the order they were selected
            practices.on('select2:select', function (e) {
                var id = e.params.data.id;
                var option = $(e.target).children('[value=' + id + ']');
                option.detach();
                $(e.target).append(option).change();
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .form-group {
            margin: 20px !important;
        }
    </style>
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">

                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Add Internal User
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                {!! Form::open(array('url' => URL::route('admin.users.store'), 'class' => 'form-horizontal')) !!}
                                <div>
                                    <div role="tabpanel" class="tab-pane active" id="program">
                                        <h2>User Information</h2>
                                        <hr>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-2">{!! Form::label('username', 'Username:') !!}</div>
                                                <div class="col-xs-4">{!! Form::text('username', '', ['class' => 'form-control']) !!}</div>

                                                <div class="col-xs-2">{!! Form::label('email', 'Email:') !!}</div>
                                                <div class="col-xs-4">{!! Form::text('email', '', ['class' => 'form-control']) !!}</div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                                <div class="col-xs-4">{!! Form::text('first_name', '', ['class' => 'form-control']) !!}</div>

                                                <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                                <div class="col-xs-4">{!! Form::text('last_name', '', ['class' => 'form-control']) !!}</div>
                                            </div>
                                        </div>

                                        <br>
                                        <br>
                                        <h2>Access Rights</h2>
                                        <hr>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-2">
                                                    Practices
                                                </div>
                                                <div class="col-xs-4">
                                                    <select id="practices" name="practices[]"
                                                            class="practices dropdown Valid form-control" multiple required>
                                                        @foreach($practices as $id => $name)
                                                            <option value="{{$id}}">{{$name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-xs-2">{!! Form::label('role', 'Role:') !!}</div>
                                                <div class="col-xs-4">{!! Form::select('role', $roles, '', ['class' => 'form-control select-picker']) !!}</div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="radio-inline">
                                                        <input id="auto_attach_programs" name="auto_attach_programs"
                                                               value="1" type="checkbox">
                                                        <label for="auto_attach_programs"><span> </span>Grant permission to all practices</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="row" style="margin-top:50px;">
                                    <div class="col-sm-12">
                                        <div class="pull-right">
                                            {!! Form::submit('Create User', array('class' => 'btn btn-success')) !!}
                                        </div>
                                    </div>
                                </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
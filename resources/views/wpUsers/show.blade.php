@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('core::partials.errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        User ID: {{ $wpUser->id }}
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>email</strong></td>
                                <td><strong>first_name</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $wpUser->email }}</td>
                                <td>need this for meta <a href="https://github.com/chrismichaels84/eloquent-meta">https://github.com/chrismichaels84/eloquent-meta</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <h1>Primary Blog = {{ $wpUser->primaryProgramId() }}</h1>

                        <h1>Meta</h1>
                        <div id="usermetas">
                            @foreach( $wpUser->meta as $i => $meta )
                                <div class="row" style="margin-top:5px;border-bottom:1px solid #555;">
                                <div class="form-group action">
                                    <div class=" col-sm-2">{!! Form::label('meta'.$i.'key', 'Meta Key:') !!}</div>
                                    <div class=" col-sm-3">{!! Form::text('meta'.$i.'key', $meta->meta_key, ['class' => 'form-control', 'style' => 'width:120px;']) !!}</div>
                                    <div class=" col-sm-2">{!! Form::label('meta'.$i.'value', 'Meta Value:') !!}</div>
                                    <div class=" col-sm-3">{!! Form::text('meta'.$i.'value', $meta->meta_value, ['class' => 'form-control', 'style' => 'width:120px;']) !!}</div>
                                    <div class=" col-sm-2">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-action')) !!}</div>
                                </div>
                                </div>
                            @endforeach
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::button('Update User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        {!! Form::close() !!}

                        <h1>Activities</h1>
                        <p>Current monthly total (recalculated on the fly) : {{ $activityTotal }} seconds</p>
                        <a href="#" class="recalcActivities"><span class="glyphicon glyphicon-refresh"></span> Recalculate Monthly Total (disabled)</a>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
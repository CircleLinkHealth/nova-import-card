@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>User Care Plan Items</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.ucp.create', array()) }}" class="btn btn-success" disabled="disabled">New User Care Plan</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All User Care Plan Items</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            <div class="col-sm-12">
                            {!! Form::open(array('url' => URL::route('admin.ucp.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                                <h2>Filter</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('filterUser', array('all' => 'All Users') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        <div class="col-xs-2 text-right">{!! Form::label('filterPCP', 'PCP:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('filterPCP', array('all' => 'All PCP') + $pcps, $filterPCP, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:50px;">
                                    <div class="col-sm-12">
                                        <div class="" style="text-align:center;">
                                            {!! Form::hidden('action', 'filter') !!}
                                            {!! Form::submit('Apply Filters', array('class' => 'btn btn-orange')) !!}
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                            </div>
                        </div>

                        

                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>items_id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>meta_key</strong></td>
                                <td><strong>meta_value</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $ucps as $ucp )
                                <tr>
                                    <td><a href="{{ URL::route('admin.ucp.show', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Detail</a></td>
                                    <td>
                                        @if($ucp->item)
                                            <a href="{{ URL::route('admin.items.show', array('id' => $ucp->items_id)) }}" class="btn btn-orange btn-xs">{{ $ucp->item->pcp->section_text }}({{ $ucp->item->pcp->pcp_id }})</a> <br> <a href="{{ URL::route('admin.items.show', array('id' => $ucp->items_id)) }}" class="btn btn-orange btn-xs" style="margin-top:5px;">{{ $ucp->item->items_text }}({{ $ucp->items_id }})</a>
                                        @else
                                            {{ $ucp->items_id }}
                                        @endif
                                    </td>
                                    <td>{{ $ucp->user_id }}</td>
                                    <td>{{ $ucp->meta_key }}</td>
                                    <td><strong>{{ $ucp->meta_value }}</strong></td>
                                    <td><a href="{{ URL::route('admin.ucp.edit', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.ucp.destroy', array('id' => $ucp->ucp_id)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if (count($ucps) > 0)
                            {!! $ucps->appends(['action' => 'filter', 'filterUser' => $filterUser, 'filterPCP' => $filterPCP])->render() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

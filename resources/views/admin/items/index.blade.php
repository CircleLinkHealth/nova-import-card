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
                        <h1>Items</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.items.create', array()) }}" class="btn btn-success">New Item</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Items</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>pcp</strong></td>
                                <td><strong>items_parent</strong></td>
                                <td><strong>qid</strong></td>
                                <td><strong>items_text</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $items as $item )
                                <tr>
                                    <td><a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-primary">Detail</a></td>
                                    <td>
                                        @if($item->pcp)
                                            <a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-orange btn-xs">{{ $item->pcp->section_text }}</a>
                                        @else
                                            {{ $item->pcp_id }}
                                        @endif
                                    </td>
                                    <td>{{ $item->items_parent }}</td>
                                    <td><a href="{{ URL::route('admin.questions.show', array('id' => $item->qid)) }}" class="btn btn-orange btn-xs">{{ $item->qid }}</a></td>
                                    <td>{{ $item->items_text }}</td>
                                    <td><a href="{{ URL::route('admin.items.edit', array('id' => $item->items_id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.items.destroy', array('id' => $item->items_id)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $items->appends(['action' => 'filter'])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

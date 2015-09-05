@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Program</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Program ID: {{ $wpBlog->blog_id }}
                    </div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (count($messages) > 0)
                            <div class="alert alert-success">
                                <strong>Messages:</strong><br><br>
                                <ul>
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.programsUpdate', array('id' => $wpBlog->blog_id)), 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::submit('Update Program', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                        <h2>Program - {{ $wpBlog->domain }}</h2>
                        <p>Program Info</p>

                        @if (isset($programItems))
                            @foreach( $programItems as $pcpId => $pcpSection )
                                <button class="btn btn-primary" style="margin:20px 0px;" type="button" data-toggle="collapse" data-target="#pcp{{ $pcpId }}" aria-expanded="false" aria-controls="pcp{{ $pcpId }}">{{ $pcpSection['section_text'] . '('.count($pcpSection['items']).')' }}</button><br />
                                <div id="pcp{{ $pcpId }}" class="collapse">
                                @if (count($pcpSection['items']) > 0)
                                    @foreach ($pcpSection['items'] as $item)
                                        <div class="alert alert-info">
                                            <button class="btn btn-primary" style="margin:20px 0px;" type="button" data-toggle="collapse" data-target="#paritem{{ $item->items_id }}" aria-expanded="false" aria-controls="paritem{{ $item->items_id }}">Details</button>
                                            <h4>Parent Item: {{ $item->items_text }} <button type="button" class="btn btn-primary btn-xs">Edit</button> <button type="button" class="btn btn-success btn-xs">Add Child Item</button> <button type="button" class="btn btn-danger btn-xs">Remove</button></h4>
                                            <div id="paritem{{ $item->items_id }}" class="collapse">
                                                [ Items Id = {{ $item->items_id }} ]<br>
                                                @if (count($item->question) > 0)
                                                    [ Msg Id = {{ $item->question->msg_id }} ]<br>
                                                    [ Obs Key = {{ $item->question->obs_key }} ]<br>
                                                @endif
                                                @if (count($item->meta) > 0)
                                                    @foreach ($item->meta as $itemmeta)
                                                        [ Meta: {{ $itemmeta->meta_key }} = {{ $itemmeta->meta_value }} ]<br>
                                                    @endforeach
                                                @endif

                                                @if (count($item->child_items) > 0)
                                                    @foreach ($item->child_items as $childItem)
                                                        <br><strong>Child of {{ $item->items_text }}: {{ $childItem->items_text }}</strong> <button type="button" class="btn btn-primary btn-xs">Edit</button> <button type="button" class="btn btn-danger btn-xs">Remove</button><br>
                                                        [ Items Id = {{ $childItem->items_id }} ]<br>
                                                        @if (count($childItem->question) > 0)
                                                            [ Msg Id = {{ $childItem->question->msg_id }} ]<br>
                                                            [ Obs Key = {{ $childItem->question->obs_key }} ]<br>
                                                        @endif
                                                        @if (count($childItem->meta) > 0)
                                                            @foreach ($childItem->meta as $childItemmeta)
                                                                [ Meta: {{ $childItemmeta->meta_key }} = {{ $childItemmeta->meta_value }} ]<br>
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                </div>
                            @endforeach
                        @endif

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::submit('Update Program', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
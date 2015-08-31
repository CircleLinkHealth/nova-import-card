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
            <div class="col-md-8 col-md-offset-2">
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
                            {!! Form::open(array('url' => '/programs/'.$wpBlog->blog_id.'/edit', 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-left">
                                    <a href="{{ url('programs/'.$wpBlog->blog_id.'/questions') }}" class="btn btn-primary">Questions</a>
                                </div>
                                <div class="pull-left" style="margin-left:10px;">
                                    <a href="{{ url('programs/'.$wpBlog->blog_id.'/users') }}" class="btn btn-primary">Users</a>
                                </div>
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::submit('Update Program', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                        <h2>Program</h2>
                        <p>Program Info</p>

                        @if (isset($programItems))
                            @foreach( $programItems as $pcpId => $pcpSection )
                                <div id="pcp{{ $pcpId }}" class="">
                                <h3>{{ $pcpSection['section_text'] }}</h3>
                                @if (count($pcpSection['items']) > 0)
                                    @foreach ($pcpSection['items'] as $item)
                                        <div class="alert alert-success">
                                            {{ $item->items_text }} | {{ $item->items_parent }} | {{ $item->qid }} | {{ $item->items_text }}<br>
                                            @if (count($item->meta) > 0)
                                                @foreach ($item->meta as $itemmeta)
                                                    <div class="alert alert-warning">
                                                        {{ $itemmeta->itemmeta_id }} - {{ $itemmeta->meta_key }} - {{ $itemmeta->meta_value }}<br>
                                                    </div>
                                                @endforeach
                                            @endif
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
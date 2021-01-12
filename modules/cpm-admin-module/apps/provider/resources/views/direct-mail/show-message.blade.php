@extends('partials.adminUI')

@section('content')
    <div class="container">
        @if(Session::has('message'))
            <div class="alert alert-info">
                <p>{{Session::pull('message')}}</p>
            </div>
        @endif

        @if(isset($dm))
                <div class="panel panel-primary">
                    <div class="panel-heading">Direct Message [{{$dm->id}}]</div>
                    <div class="panel-body">
                        @if (isset($links) && $links->isNotEmpty())
                            <p><b>Regarding CPM Patient(s)</b></p>
                            @foreach($links as $link)
                                <p>{!! $link !!}</p>
                            @endforeach
                        @endif
                        <p><b>Subject:</b> {{$dm->subject}}</p>
                        <p><b>From:</b> {{$dm->from}}</p>
                        <p><b>To:</b> {{$dm->to}}</p>
                        <p><b>Number of attachments:</b> {{$dm->num_attachments}}</p>
                        <p><b>Message ID:</b> {{$dm->message_id}}</p>
                        <p><b>Direction:</b> {{ucfirst($dm->direction)}}</p>
                        <p><b>Status:</b> {{ucfirst($dm->status)}}</p>
                        <p><b>Message:</b> {!! $dm->body !!}</p>

                        <br><br>

                        <h3>CCDAs</h3>
                        @forelse($dm->ccdas as $ccda)
                            <a href="{{route('get.CCDViewerController.show', [$ccda->id])}}">CCDA [{{$ccda->id}}]</a>
                            @if($ccda->imported)
                                was already imported.
                            @else
                                has not been imported yet.
                            @endif
                            <a href="{{route('download.ccda.xml', [$ccda->id])}}">CCDA [{{$ccda->id}}] Download RAW XML</a>
                        @empty
                            No CCDAs were sent with the message.
                        @endforelse


                        <br><br>

                        <h3>Other Attachments</h3>
                        @forelse($dm->media as $media)
                            <a href="{{$media->getUrl()}}">Attachment [{{$media->id}}]</a>
                        @empty
                            No other attachments were sent with the message.
                        @endforelse
                    </div>
                </div>
        @endif

        <div class="panel panel-primary">
            <div class="panel-heading">Send a new DM</div>

            <div class="panel-body">
                <form method="POST" action="{{route('direct-mail.send')}}">
                    {{csrf_field()}}

                    <div class="form-group">
                        <label for="dm_subject">Subject</label>
                        <input type="text" class="form-control" id="dm_subject" name="dm_subject" placeholder="Subject" required>
                    </div>
                    <div class="form-group">
                        <label for="dm_from_address">From</label>
                        <input type="email" class="form-control" id="dm_from_address" name="dm_from_address" placeholder="DM Address" value="{{isset($dm) ? $dm->to : config('services.emr-direct.user')}}" required>
                    </div>
                    <div class="form-group">
                        <label for="dm_to_address">To</label>
                        <input type="email" class="form-control" id="dm_to_address" name="dm_to_address" placeholder="DM Address" value="{{isset($dm) ? $dm->from : ''}}" required>
                    </div>
{{--                    <div class="form-group">--}}
{{--                        <label for="dm_ccda_upload">Upload CCDA</label>--}}
{{--                        <input type="file" id="dm_ccda_upload" name="dm_ccda_upload">--}}
{{--                        <p class="help-block">Do not send real patient data to test DM addresses.</p>--}}
{{--                    </div>--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="dm_ccda_other_file">Upload Other File</label>--}}
{{--                        <input type="file" id="dm_ccda_other_file" name="dm_ccda_other_file" multiple>--}}
{{--                        <p class="help-block">Do not send real patient data to test DM addresses.</p>--}}
{{--                    </div>--}}
                    <div class="form-group">
                        <label for="dm_body">Message</label>
                        <textarea class="form-control" id="dm_body" name="dm_body" placeholder="DM Body" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-default">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection



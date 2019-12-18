@extends('partials.adminUI')

@section('content')
    <div class="container">
        <div class="panel panel-primary">
            <div class="panel-heading">Direct Message [{{$dm->id}}]</div>
            <div class="panel-body">
                <p><b>Message ID:</b> {{$dm->message_id}}</p>
                <p><b>Number of attachments:</b> {{$dm->num_attachments}}</p>
                <p><b>From:</b> {{$dm->from}}</p>
                <p><b>To:</b> {{$dm->to}}</p>
                <p><b>Subject:</b> {{$dm->subject}}</p>
                <p><b>Message:</b> {{$dm->body}}</p>

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
    </div>
@endsection



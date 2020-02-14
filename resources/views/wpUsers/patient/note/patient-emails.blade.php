@if($patientEmails)
    @foreach($patientEmails as $email)
        <div class="form-group col-md-12" style="margin-bottom: 40px">
            <div style="margin-bottom: 5px;">
                <span style="color: #50b2e2">{{$email['senderFullName']}}</span>
                sent an email
                on <span style="color: lightgrey">{{$email['created_at']}}</span>
            </div>
            <div>
                <span><strong>Subject:</strong>&nbsp; {{$email['subject']}}</span>
            </div>
            <div class="col-md-12"
                 style="margin-bottom: 10px; margin-top: 10px; background-color: #eee; border: 1px solid lightgrey; border-radius: 3px">
                                             <span style="margin-top: 5px">
                                            {!! $email['content'] !!}
                                             </span>
                <div style="margin-bottom: 10px; margin-top: 10px">
                    @if(isset($email['attachments']))
                        <hr style="border-top: 1px solid lightgrey">
                        <div>
                            @foreach($email['attachments'] as $attachment)
                                <div class="col-md-3"
                                     style="max-height: 250px; max-width: 250px">
                                    <a href="{{$attachment['url']}}" target="_blank">
                                        @if($attachment['is_image'])
                                        <img style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);"
                                            src="{{$attachment['url']}}">
                                            @else
                                            <i class="far fa-file-alt"></i> &nbsp;{{$attachment['file_name']}}
                                        @endif
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-12" style="margin-top: 20px"></div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif
<div class="flow-text" style="max-height: 590px; overflow-y: scroll;">
    <div style="display: inline-flex">
        <div class="header" style="padding-right: 190px; font-size: 23px;">
            {{$signatoryNameForHeader}}
            <br>
            {{$practiceName}}
       @if($extraAddressValuesRequested)
                <br>
                {{$extraAddressValues[0]['address_line_1']}}
                <br>
                {{$extraAddressValues[0]['city']}}
                {{$extraAddressValues[0]['state']}}
            @endif
        </div>
        <div class="logo" style="{{$logoStyleRequest}}; opacity: 90%;">
            @include('enrollment-consent.practiceLogo')
        </div>
    </div>
</div>

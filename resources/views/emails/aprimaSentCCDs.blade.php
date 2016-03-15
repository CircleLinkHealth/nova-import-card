<div>
    <br>CCDA ID: {{ $ccdId }}
    @if(isset($line))
        <br>Line: {{ $line }}
    @endif
    @if(isset($errorMessage))
        <br>Exception Message: {{ $errorMessage }}
    @endif
</div>
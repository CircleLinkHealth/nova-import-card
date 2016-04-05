<div>
    <br>CCDA ID: {{ $ccdId }}
    <br>Visit <a href="{{ route('view.files.ready.to.import') }}" target="_blank">the Importer's Summary Page</a> to import the CCD.
    @if(isset($line))
        <br>Line: {{ $line }}
    @endif
    @if(isset($errorMessage))
        <br>Exception Message: {{ $errorMessage }}
    @endif
</div>
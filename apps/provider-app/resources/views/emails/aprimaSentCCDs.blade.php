<div>
    <br>CCDA ID: {{ $ccdId }}
    <br>Visit <a href="{{ route('import.ccd.remix') }}" target="_blank">the Importer's Summary Page</a> to
    import the CCD.
    @if(isset($line))
        <br>Line: {{ $line }}
    @endif
    @if(isset($errorMessage))
        <br>Exception Message: {{ $errorMessage }}
    @endif
    @if(isset($providerInfo))
        <br>Provider Info: {{ $providerInfo }}
    @endif

</div>
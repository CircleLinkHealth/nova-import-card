<div>
    <br>Visit <a href="{{ route('import.ccd.remix') }}" target="_blank">the Importer's Summary Page</a> to
    import these CCDs.
    @if(isset($numberOfCcds))
        <br>Number of CCDs received: {{ $numberOfCcds }}
    @endif
    @if(isset($ccdas))
        @foreach($ccdas as $ccda)
            <br>
            <br>CCD Id: <b>{{ $ccda['id'] }}</b>
            <br>Filename: <b>{{ $ccda['fileName'] }}</b>
            <br>
        @endforeach
    @endif
</div>
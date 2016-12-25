<h3>Practice Demographics</h3>

<p>Your team has {{$data[$practiceSection]['lead']}} lead(s): N/A. In total, there
    are {{$data[$practiceSection]['total']}} members on your CCM team (that’s not
    including {{$data[$practiceSection]['disabled']}} disabled users).
</p>
@if($data[$practiceSection]['total'] != 0)

    <p>Of the active users, {{$data[$practiceSection]['providers']}} are Providers, {{$data[$practiceSection]['cc']}}
        are
        RNs, {{$data[$practiceSection]['oas']}} are office staff and {{$data[$practiceSection]['mas']}} are MAs.
    </p>

@endif
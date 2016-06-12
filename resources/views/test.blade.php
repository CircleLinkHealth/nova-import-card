@foreach ($results as $result)
    <p>{{ $result }}</p>
@endforeach

//Show pagination links
{!! $results->render() !!}
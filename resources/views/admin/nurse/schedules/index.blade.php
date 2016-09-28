@extends('partials.adminUI')

@section('content')
    <div class="container">
        @foreach($windows as $windowCollection)
            <div class="row">
                <h3>{{ $windowCollection->first()->nurse->user->fullName }}</h3>

                @foreach($windowCollection as $w)
                    <div class="row">
                        @include('partials.care-center.work-schedule-slot.admin-edit', [ 'window' => $w ])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
@extends('partials.adminUI')

@section('content')
    <div class="container">
        @foreach($windows as $windowCollection)
            <div class="row" style="padding-bottom: 5%;">
                <h3>
                    <b>{{ $windowCollection->first()->nurse->user->fullName }}</b>
                    <span class="pull-right red-text">Timezone: {{ $windowCollection->first()->nurse->user->timezone }}</span>
                </h3>

                <div class="row">
                    @include('partials.care-center.work-schedule-slot.admin-store', [ 'nurseInfo' => $windowCollection->first()->nurse ])
                </div>

                <h4>Existing Windows</h4>

                @foreach($windowCollection as $w)
                    <div class="row">
                        @include('partials.care-center.work-schedule-slot.admin-edit', [ 'window' => $w ])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
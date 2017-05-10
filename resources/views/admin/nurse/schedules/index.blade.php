@extends('partials.adminUI')

@section('content')
    @include('errors.errors')

    <div class="container">
        @foreach($data as $d)
            <div class="row" style="padding-bottom: 10%;">
                <h3>
                    <b>{{ $d->fullName }}</b>
                    <span class="pull-right red-text">Timezone: {{ $d->timezone ? $d->timezone : 'Not set' }}</span>
                </h3>

                <div class="row">
                    @include('partials.care-center.work-schedule-slot.admin-store', [ 'nurseInfo' => $d->nurseInfo ])
                </div>

                @if($d->nurseInfo->windows->count() > 0)
                    <h4>Existing Windows</h4>
                @else
                    <h4>This nurse does not have any windows.</h4>
                @endif

                @foreach($d->nurseInfo->windows as $w)
                    <div class="row">
                        @include('partials.care-center.work-schedule-slot.admin-edit', [ 'window' => $w ])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
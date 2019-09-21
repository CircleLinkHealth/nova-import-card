@extends('partials.adminUI')

@section('content')

    <div style="position: fixed;bottom: 0;width: 100%;z-index: 100;opacity: 0.95;">
        @include('errors.errors')
    </div>

    <div class="container">
        <nurse-schedule-calendar :calendar-data="{{json_encode($calendarData)}}"></nurse-schedule-calendar>
    </div>
@endsection

@push('scripts')

    @if(session('editedNurseId'))
        <script>
            (function () {
                window.location.hash = '#nurse-{{session('editedNurseId')}}';
            })();
        </script>
    @endif
@endpush
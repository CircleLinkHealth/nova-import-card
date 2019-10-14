@extends('partials.adminUI')

@section('content')

    <div style="position: fixed;bottom: 0;width: 100%;z-index: 100;opacity: 0.95;">
        @include('errors.errors')
    </div>

    <div class="container">

        <nurse-schedule-calendar :calendar-data="{{json_encode($calendarData)}}"
                                 :data-for-dropdown="{{json_encode($dataForDropdown)}}"
                                 :today="{{json_encode($today)}}"
                                 :start-of-month="{{json_encode($startOfMonth)}}"
                                 :end-of-month="{{json_encode($endOfMonth)}}"
                                 :year="{{json_encode($year)}}"></nurse-schedule-calendar>
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
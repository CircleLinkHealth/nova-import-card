@extends('cpm-admin::partials.adminUI')

@section('content')

    <div style="position: fixed;bottom: 0;width: 100%;z-index: 100;opacity: 0.95;">
        @include('core::partials.errors.errors')
    </div>
    <nurse-schedule-calendar
            :auth-data="{{json_encode($authData)}}"
            :today="{{json_encode($today)}}">
    </nurse-schedule-calendar>
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

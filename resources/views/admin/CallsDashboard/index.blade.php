@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush

    <div class="container">
        <h3 align="center">Edit Call Status or create new Call for Note</h3>
        <hr><br>
        <div class="text-center">
            <div>
                <form action="{{route('CallsDashboard.create')}}">
                    <h4 class="ops-dboard-title">Insert Note ID to begin Operations</h4>
                    <div class="form-group">
                        @if (session('msg'))
                            <div class="alert alert-success">
                                {{ session('msg') }}
                            </div>
                        @endif
                    </div>
                    <br>
                    Note ID:
                    <input type="number" name="noteId" value="" required>
                    <br>
                    <input align="center" type="submit" value="Submit" class="btn btn-info">
                </form>
            </div>

        </div>
    </div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Welcome</div>

                    <div class="card-body">
                        You are logged in!
                        <div class="alert alert-success" role="alert">
                            Please click the link in the SMS or email you received in order to start the questionnaire.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

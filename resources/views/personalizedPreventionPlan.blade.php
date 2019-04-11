@extends('surveysMaster')

@section('content')
    <div class="container">
      {{$pppData->display_name}}
        {{$pppData->birth_date}}
        {{$pppData->address}}
        {{$pppData->user_id}}
        {{$pppData->billing_provider}}
        {{$pppData->hra_values}}
        {{$pppData->vitals_values}}
    </div>
@endsection
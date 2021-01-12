@extends('errors::layout')

@section('title', 'Page Expired')

@section('message')
    The page has expired due to inactivity or a user logout on a different browser tab.
    <br/><br/>
    Please refresh or go to our <a href="/">Home Page</a>.
@stop

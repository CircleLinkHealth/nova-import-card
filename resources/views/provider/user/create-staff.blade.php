@extends('provider.layouts.dashboard')

@section('title', 'Invite Staff Members')

@section('module')

    @include('errors.errors')

    <div class="mdl-layout mdl-js-layout">

        <main class="mdl-layout__content mdl-cell--4-col">

            {!! Form::open(['url' => route('post.store.invite'), 'method' => 'post']) !!}

            @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'email', 'label' => 'Email', 'value' => $invite->email ])

            <div class="mdl-layout-spacer"></div>

            @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'subject', 'label' => 'Subject', 'value' => $invite->subject ])

            <div class="mdl-layout-spacer"></div>

            @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'message', 'label' => 'Message', 'value' => $invite->message ])

            <div class="mdl-layout-spacer"></div>

            @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'role', 'label' => 'Role', 'value' => $invite->role ])

            <div class="mdl-layout-spacer"></div>

            <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
                Send Invite
            </button>

            {!! Form::close() !!}


        </main>

    </div>

@endsection
@extends('provider.layouts.dashboard')

@section('title', 'Invite Staff Members')

@section('module')

    @include('errors.errors')

    <section class="mdl-cell--4-col">

        {!! Form::open(['url' => route('post.store.invite'), 'method' => 'post']) !!}

        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'email', 'label' => 'Email', 'value' => $invite->email, 'class' => 'mdl-cell--12-col' ])

        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'subject', 'label' => 'Subject', 'value' => $invite->subject, 'class' => 'mdl-cell--12-col'  ])

        @include('provider.partials.mdl.form.text.textarea', [ 'name' => 'message', 'label' => 'Message', 'value' => $invite->message, 'class' => 'mdl-cell--12-col'  ])

        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'role', 'label' => 'Role', 'value' => $invite->role, 'class' => 'mdl-cell--12-col'  ])

        <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col">
            Send Invite
        </button>

        {!! Form::close() !!}


    </section>


@endsection
{!! Form::open(['url' => route('post.store.location'), 'method' => 'post']) !!}
<i class="material-icons">mode_edit</i>
<div class="mdl-grid">
    <div class="mdl-cell mdl-cell--12-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'name', 'label' => 'Name', 'value' => $location['name'] ])
    </div>

    <div class="mdl-cell mdl-cell--12-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'address_line_1', 'label' => 'Address Line 1', 'value' => $location['address_line_1'] ])
    </div>

    <div class="mdl-cell mdl-cell--12-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'address_line_2', 'label' => 'Address Line 2', 'value' => $location['address_line_2'] ])
    </div>

    <div class="mdl-cell mdl-cell--6-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'city', 'label' => 'City', 'value' => $location['city'] ])
    </div>

    <div class="mdl-cell mdl-cell--6-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'state', 'label' => 'State', 'value' => $location['state'] ])
    </div>

    <div class="mdl-cell mdl-cell--6-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'postal_code', 'label' => 'Postal Code', 'value' => $location['postal_code'] ])
    </div>

    <div class="mdl-cell mdl-cell--6-col">
        @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'phone', 'label' => 'Phone', 'value' => $location['phone'] ])
    </div>
</div>

<button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
    Update Practice
</button>

{!! Form::close() !!}


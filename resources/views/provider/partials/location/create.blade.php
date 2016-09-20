{!! Form::open(['url' => route('post.store.location'), 'method' => 'post']) !!}

@include('provider.partials.mdl.form.text.textfield', [ 'name' => 'name', 'label' => 'Name', 'value' => $location['name'] ])
<div class="mdl-layout-spacer"></div>

@include('provider.partials.mdl.form.text.textfield', [ 'name' => 'address_line_1', 'label' => 'Address Line 1', 'value' => $location['address_line_1'] ])
<div class="mdl-layout-spacer"></div>

@include('provider.partials.mdl.form.text.textfield', [ 'name' => 'address_line_2', 'label' => 'Address Line 2', 'value' => $location['address_line_2'] ])
<div class="mdl-layout-spacer"></div>

<div class="mdl-cell--6-col">
    @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'city', 'label' => 'City', 'value' => $location['city'] ])
</div>
<div class="mdl-cell--6-col">
    @include('provider.partials.mdl.form.text.textfield', [ 'name' => 'state', 'label' => 'State', 'value' => $location['state'] ])
</div>
<div class="mdl-layout-spacer"></div>


<button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
    Update Practice
</button>

{!! Form::close() !!}


'phone',

'city',
'state',
'postal_code',
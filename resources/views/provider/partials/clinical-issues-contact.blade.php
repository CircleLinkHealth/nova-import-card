@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'firstName',
    'label' => 'First name',
    'class' => 'col s6',
    'attributes' => [
        'required' => 'required',
    ]
])

@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'lastName',
    'label' => 'Last name',
    'class' => 'col s6',
    'attributes' => [
        'required' => 'required',
    ]
])

@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'instead_of_billing_provider_email',
    'label' => 'E-mail',
    'class' => 'col s6',
    'type' => 'email',
])

@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'instead_of_billing_provider_phone',
    'label' => 'Phone',
    'class' => 'col s6'
])
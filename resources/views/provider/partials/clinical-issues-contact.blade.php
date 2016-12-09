@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'locations[@{{index}}][clinical_contact][firstName]',
    'label' => 'First name',
    'class' => 'col s6',
    'attributes' => [
        'required' => 'required',
    ]
])

@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'locations[@{{index}}][clinical_contact][lastName]',
    'label' => 'Last name',
    'class' => 'col s6',
    'attributes' => [
        'required' => 'required',
    ]
])

@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'locations[@{{index}}][clinical_contact][email]',
    'label' => 'E-mail',
    'class' => 'col s6',
    'type' => 'email',
])

@include('provider.partials.mdl.form.text.textfield', [
    'name' => 'locations[@{{index}}][clinical_contact][phone]',
    'label' => 'Phone',
    'class' => 'col s6'
])
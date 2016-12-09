<div class="row">
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
</div>

<div class="row">
    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'locations[@{{index}}][clinical_contact][email]',
        'label' => 'E-mail',
        'class' => 'col s12',
        'type' => 'email',
    ])
</div>
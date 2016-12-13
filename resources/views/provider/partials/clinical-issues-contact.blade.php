<div class="row">
    @include('provider.partials.mdl.form.text.textfield', [
    'name' => 'locations[@{{index}}][clinical_contact][firstName]',
    'label' => 'First name',
    'class' => 'col s6',
    'attributes' => [
        'required' => 'required',
        'v-on:change' => 'isValidated(index)',
            'v-on:invalid' => 'isValidated(index)',
            'v-on:keyup' => 'isValidated(index)',
            'v-on:click' => 'isValidated(index)',
    ]
])

    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'locations[@{{index}}][clinical_contact][lastName]',
        'label' => 'Last name',
        'class' => 'col s6',
        'attributes' => [
            'required' => 'required',
            'v-on:change' => 'isValidated(index)',
            'v-on:invalid' => 'isValidated(index)',
            'v-on:keyup' => 'isValidated(index)',
            'v-on:click' => 'isValidated(index)',
        ]
    ])
</div>

<div class="row">
    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'locations[@{{index}}][clinical_contact][email]',
        'label' => 'E-mail',
        'class' => 'col s12',
        'type' => 'email',
        'attributes' => [
            'required' => 'required',
            'v-on:change' => 'isValidated(index)',
            'v-on:invalid' => 'isValidated(index)',
            'v-on:keyup' => 'isValidated(index)',
            'v-on:click' => 'isValidated(index)',
        ]
    ])
</div>
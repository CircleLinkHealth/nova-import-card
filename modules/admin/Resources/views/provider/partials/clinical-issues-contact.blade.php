<div class="row">
    @include('cpm-admin::provider.partials.mdl.form.text.textfield', [
    'name' => 'locations[@{{index}}][clinical_contact][first_name]',
    'label' => 'First name',
    'class' => 'col s6',
    'value' => '@{{loc.clinical_contact.first_name}}',
    'attributes' => [
        'required' => 'required',
        'v-model' => 'loc.clinical_contact.first_name',
        'v-on:change' => 'isValidated(index)',
        'v-on:invalid' => 'isValidated(index)',
        'v-on:keyup' => 'isValidated(index)',
        'v-on:click' => 'isValidated(index)',
    ],
])

    @include('cpm-admin::provider.partials.mdl.form.text.textfield', [
        'name' => 'locations[@{{index}}][clinical_contact][last_name]',
        'label' => 'Last name',
        'class' => 'col s6',
        'value' => '@{{loc.clinical_contact.last_name}}',
        'attributes' => [
            'required' => 'required',
            'v-model' => 'loc.clinical_contact.last_name',
            'v-on:change' => 'isValidated(index)',
            'v-on:invalid' => 'isValidated(index)',
            'v-on:keyup' => 'isValidated(index)',
            'v-on:click' => 'isValidated(index)',
        ]
    ])
</div>

<div class="row">
    @include('cpm-admin::provider.partials.mdl.form.text.textfield', [
        'name' => 'locations[@{{index}}][clinical_contact][email]',
        'value' => '@{{loc.clinical_contact.email}}',
        'label' => 'E-mail',
        'class' => 'col s12',
        'type' => 'email',
        'attributes' => [
            'v-model' => 'loc.clinical_contact.email',
            'required' => 'required',
            'v-on:change' => 'isValidated(index)',
            'v-on:invalid' => 'isValidated(index)',
            'v-on:keyup' => 'isValidated(index)',
            'v-on:click' => 'isValidated(index)',
        ]
    ])
</div>
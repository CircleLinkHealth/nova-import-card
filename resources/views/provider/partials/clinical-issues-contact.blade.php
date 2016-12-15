<div class="row">
    @include('provider.partials.mdl.form.text.textfield', [
    'label' => 'First name',
    'class' => 'col s6',
    'attributes' => [
        'required' => 'required',
        'v-model' => 'loc.clinical_contact.firstName',
        'v-on:change' => 'isValidated(index)',
        'v-on:invalid' => 'isValidated(index)',
        'v-on:keyup' => 'isValidated(index)',
        'v-on:click' => 'isValidated(index)',
    ]
])

    @include('provider.partials.mdl.form.text.textfield', [
        'label' => 'Last name',
        'class' => 'col s6',
        'attributes' => [
            'required' => 'required',
            'v-model' => 'loc.clinical_contact.lastName',
            'v-on:change' => 'isValidated(index)',
            'v-on:invalid' => 'isValidated(index)',
            'v-on:keyup' => 'isValidated(index)',
            'v-on:click' => 'isValidated(index)',
        ]
    ])
</div>

<div class="row">
    @include('provider.partials.mdl.form.text.textfield', [
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
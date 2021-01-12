<style>
    .modal label {
        font-size: 14px;
    }

    .providerForm {
        padding: 10px;
    }

    .validation-error {
        padding: 3px;
        margin-bottom: 10px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
</style>

<template>

    <div>
        <div v-bind:id="'successModal-' + care_person.id" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Care Team Updated</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            The changes you made to {{ care_person.user.first_name }} {{ care_person.user.last_name }}
                            will
                            be reflected on the patient's care team.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>


        <div v-bind:id="'editCareTeamModal-' + care_person.id" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Provider Details</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row providerForm">
                            <search-providers v-if="!care_person.user.id"
                                              v-bind:first_name="care_person.user.first_name"
                                              v-bind:last_name="care_person.user.last_name"
                            ></search-providers>
                        </div>


                        <vue-form :state="formstate" @submit.prevent="onSubmit">
                            <div class="row providerForm">
                                <div class="form-group">

                                    <label class="col-md-3 control-label">Provider Name</label>

                                    <div id="provider-name" class="col-md-9">
                                        <validate auto-label class="form-group required-field col-md-6"
                                                  :class="fieldClassName(formstate.name)">
                                            <input type="text"
                                                   id="first_name"
                                                   name="first_name"
                                                   class="form-control input-md"
                                                   placeholder="First"
                                                   required
                                                   v-model.lazy="care_person.user.first_name">

                                            <field-messages name="first_name" show="$touched || $submitted"
                                                            class="form-control-feedback">
                                                <div>Success!</div>
                                                <div class="validation-error alert-danger text-right" slot="required">*required</div>
                                            </field-messages>

                                        </validate>
                                    </div>

                                </div>
                                </div>

                        </vue-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    let Vue = require('vue');
    Vue.use(require('vue-resource'));

    Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

    //Load components
    require('./search-providers.vue');

    // This is the event hub we'll use in every
    // component to communicate between them.
    let eventHub = new Vue();

    let carePerson = Vue.component('carePerson', {
        created: function () {
            eventHub.$on('existing-user-selected', this.updateOldBillingProviderSpecialty);
        },

        beforeDestroy: function () {
            eventHub.$off('existing-user-selected', this.updateOldBillingProviderSpecialty);
        },

        template: '',

        props: [
            //This is an CircleLinkHealth\Customer\Entities\CarePerson Object with relationships User and ProviderInfo loaded.
            'care_person',
        ],

        data: function () {
            return {
                addCarePersonForm: {},
                patientId: '',
                updateRoute: '',
                id: '',
                formatted_type: '',

                formstate: {},
                model: {
                    first_name: '',
                    last_name: '',
                    address: '',
                    address_2: '',
                    city: '',
                    state: '',
                    zip: '',
                    phone: '',
                    practice: '',
                    email: '',
                    is_clinical: '',
                    send_alerts: '',
                    is_billing_provider: '',
                }
            }
        },

        mounted: function () {
            this.updateRoute = $('meta[name="provider-update-route"]').attr('content');
            this.patientId = $('meta[name="patient_id"]').attr('content');
            this.selectSpecialty(this.care_person.user.provider_info.specialty);
        },

        methods: {
            fieldClassName: function (field) {
                if (!field) {
                    return '';
                }
                if ((field.$touched || field.$submitted) && field.$valid) {
                    return 'has-success';
                }
                if ((field.$touched || field.$submitted) && field.$invalid) {
                    return 'has-danger';
                }
            },

            //This runs when a a different billing provider was selected, to update the data and reflect that change on the page.
            updateOldBillingProviderSpecialty: function (data) {
                this.care_person.user = data.user;
                this.selectSpecialty(data.user.provider_info.specialty);
            },

            //programmatically select specialty in select box
            selectSpecialty: function (specialty) {
                let selectId = '#specialty-' + this.care_person.id;
                //Update select box
                $(selectId).val(specialty).trigger("change");
            },

            updateCarePerson: function (id) {

                if (this.care_person.is_billing_provider) {
                    this.care_person.formatted_type = 'Billing Provider';
                }

                this.$http.patch(this.updateRoute + '/' + id, {
                    careTeamMember: this.care_person,
                    patientId: this.patientId,
                }).then(function (response) {
                    carePerson.care_person.id = response.data.carePerson.id;
                    carePerson.care_person.formatted_type = response.data.carePerson.formatted_type;

                    $("#editCareTeamModal-" + id).modal('hide');

                    if (response.data.oldBillingProvider) {
                        eventHub.$emit('existing-user-selected', {
                            oldBillingProvider: response.data.oldBillingProvider,
                        });
                    }

                    $("#successModal-" + id).modal({    backdrop: 'static',    keyboard: false});

                    //HACK to replace select2 with newly added provider on appointments page
                    let carePerson = response.data.carePerson;

                    $('#providerBox').replaceWith('<select id="provider" ' +
                        'name="provider"' +
                        'class="provider selectpickerX dropdownValid form-control" ' +
                        'data-size="10" disabled>  ' +
                        '<option value="' + carePerson.user.id + '" selected>' + carePerson.user.first_name + ' ' + carePerson.user.last_name + '</option></select>');

                    $('#providerDiv').css('padding-bottom', '10px');
                    $("#save").append('<input type="hidden" value="' + carePerson.user.id + '" id="provider" name="provider">');
                    $("#addProviderModal").modal('hide');

                    $("#newProviderName").text(carePerson.name);

                }, function (response) {
                    //error
                });
            }
        }
    });

    module.exports = carePerson;
</script>
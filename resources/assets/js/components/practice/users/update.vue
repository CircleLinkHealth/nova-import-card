<template>
    <div>
        <div class="row">
            <div class="col s12">
                <h5 class="left">
                    <div v-if="formData.id === 'new'">
                        Add Staff Member
                    </div>
                    <div v-else>
                        Edit Staff Member
                    </div>
                </h5>

                <div @click="submitForm()"
                     class="btn green waves-effect waves-light right">
                    Save
                </div>

                <div @click="close()"
                     class="btn red waves-effect waves-light right"
                     style="margin-right: 2rem;">
                    Close
                </div>
            </div>
        </div>

        <div class="row">
            <div id="edit-staff-form">
                <div class="row">
                    <div class="input-field col s4">
                        <v-input type="text" label="First Name" v-model="formData.first_name" name="first_name"
                                 required></v-input>
                    </div>

                    <div class="input-field col s4">
                        <v-input type="text" label="Last Name" v-model="formData.last_name" name="last_name"
                                 required></v-input>
                    </div>

                    <div class="input-field col s4">
                        <material-select v-model="formData.role" name="role" id="role"
                                         :class="isValid(formData.role)">
                            <option v-for="option in roleOptions" :value="option.value"
                                    v-text="option.name"></option>
                        </material-select>

                        <label for="role">Role</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s4">
                        <v-input type="number" label="Phone Number" v-model="formData.phone_number" name="phone_number"
                                 required></v-input>
                    </div>

                    <div class="input-field col s4">
                        <v-input type="number" label="Phone Extension" v-model="formData.phone_extension"
                                 name="phone_extension"
                                 required></v-input>
                    </div>

                    <div class="input-field col s4">
                        <material-select v-model="formData.phone_type" name="phone_type" id="phone_type"
                                         :class="isValid(formData.phone_type)">
                            <option v-for="option in phoneTypes" :value="option.value"
                                    v-text="option.name"></option>
                        </material-select>

                        <label for="phone_type">Phone Type</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s4">
                        <v-input type="email" label="EMR Direct Address" v-model="formData.emr_direct_address" name="emr_direct_address"
                                 required></v-input>
                    </div>

                    <div class="input-field col s4">
                        <v-input type="email" label="Email" v-model="formData.email" name="email"
                                 required></v-input>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import modal from '../../shared/materialize/modal.vue';
    import {mapGetters, mapActions} from 'vuex'
    import {clearOpenModal, addNotification, updatePracticeStaff, clearErrors} from '../../../store/actions'
    import {errors, practiceStaff} from '../../../store/getters'
    import MaterialSelect from '../../src/material-select.vue'

    export default {
        props: {
            editedStaffMember: {
                type: Object,
                default: () => {
                    return {}
                }
            }
        },

        components: {
            modal,
            MaterialSelect
        },

        created() {
            if (!_.isEmpty(this.editedStaffMember)) {
                this.formData = JSON.parse(JSON.stringify(this.editedStaffMember))
            }
        },

        computed: Object.assign(
            mapGetters({
                errors: 'errors',
                staff: 'practiceStaff'
            })
        ),

        methods: Object.assign(
            mapActions(['clearOpenModal', 'addNotification', 'updatePracticeStaff', 'clearErrors']),
            {
                submitForm() {
                    this.updatePracticeStaff(this.formData)

                    Vue.nextTick(() => {
                        setTimeout(() => {
                            if (!this.errors.any()) {
                                Materialize.toast(this.formData.name + ' was successfully updated.', 3000)
                                this.close()
                            }
                        }, 500);
                    })


                },

                isValid(field) {
                    return {
                        invalid: this.errors.get(field)
                    }
                },

                isActive(field) {
                    return {
                        active: this.formData[field],
                    }
                },

                close() {
                    this.clearErrors()
                    this.$emit('update-view', 'index-staff', {})
                }
            }
        ),

        data() {
            return {
                formData: {
                    role: '',
                    locations: [],
                    grandAdminRights: false,
                    sendBillingReports: false,
                    emr_direct_address: '',
                    phone_number: '',
                    phone_extension: '',
                    phone_type: '',
                    forward_alerts_to: {
                        who: 'billing_provider',
                        user_id: ''
                    },
                    forward_careplan_approval_emails_to: {
                        who: 'billing_provider',
                        user_id: ''
                    },
                },
                formState: {},
                roleOptions: [{
                    name: 'Medical Assistant',
                    value: 'med_assistant'
                }, {
                    name: 'Office Admin',
                    value: 'office_admin'
                }, {
                    name: 'Provider',
                    value: 'provider'
                }, {
                    name: 'Registered Nurse',
                    value: 'registered-nurse'
                }, {
                    name: 'Specialist',
                    value: 'specialist'
                }],
                phoneTypes: [
                    {
                        name: 'Home',
                        value: 1
                    },
                    {
                        name: 'Mobile',
                        value: 2
                    },
                    {
                        name: 'Work',
                        value: 3
                    }
                ]
            }
        },
    }
</script>

<style>
    .invalid {
        border-bottom: 1px solid #f44336;
        box-shadow: 0 1px 0 0 #f44336;
    }
</style>
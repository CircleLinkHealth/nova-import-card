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
                    Save & Close
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
                        <v-input type="email" label="Email" v-model="formData.email" name="email"
                                 required></v-input>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s6">
                        <material-select v-model="formData.role_name" name="role_name" id="role_name"
                                         :class="isValid(formData.role_name)" :items="roleOptions">
                        </material-select>

                        <label for="role_name">Role</label>
                    </div>

                    <div class="input-field col s3">
                        <input type="checkbox" class="filled-in" id="grantAdminRights"
                               v-model="formData.grantAdminRights" :checked="formData.grantAdminRights"/>
                        <label for="grantAdminRights">Grant Admin Rights</label>
                    </div>

                    <div class="input-field col s3">
                        <input type="checkbox" class="filled-in" id="sendBillingReports"
                               v-model="formData.sendBillingReports" :checked="formData.sendBillingReports"/>
                        <label for="sendBillingReports">Send Billing Reports</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s3">
                        <v-input type="number" label="Phone Number" v-model="formData.phone_number"
                                 name="phone_number"></v-input>
                    </div>

                    <div class="input-field col s2">
                        <v-input type="number" label="Phone Extension" v-model="formData.phone_extension"
                                 name="phone_extension"></v-input>
                    </div>

                    <div class="input-field col s2">
                        <material-select v-model="formData.phone_type" name="phone_type" id="phone_type"
                                         :class="isValid(formData.phone_type)">
                            <option v-for="option in phoneTypes" :value="option.value"
                                    v-text="option.name"></option>
                        </material-select>

                        <label for="phone_type">Phone Type</label>
                    </div>

                    <div class="input-field col s5">
                        <v-input type="email" label="EMR Direct Address" v-model="formData.emr_direct_address"
                                 name="emr_direct_address"></v-input>
                    </div>
                </div>

                <div v-show="formData.role_name == 'provider'">
                    <div class="row">
                        <h6 class="col s12">
                            Whom should we notify for clinical issues regarding provider’s patients?
                        </h6>

                        <div class="input-field col s6">
                            <material-select v-model="formData.forward_alerts_to.who" class="input-field"
                                             name="forward_alerts_to.who">
                                <option v-for="option in contactOptions" :value="option.value"
                                        v-text="option.name"></option>
                            </material-select>
                        </div>

                        <div v-show="formData.forward_alerts_to.who !== 'billing_provider'" class="input-field col s6">
                            <material-select v-model="formData.forward_alerts_to.user_id" class="input-field"
                                             name="forward_alerts_to.user_id">
                                <option v-for="user in staff" :value="user.id" v-if="user.id !== formData.id"
                                        v-text="user.full_name"></option>
                            </material-select>
                        </div>
                    </div>

                    <div class="row">
                        <h6 class="col s12">
                            Whom should we notify for approval of care plans regarding provider’s patients?
                        </h6>

                        <div class="input-field col s6">
                            <material-select v-model="formData.forward_careplan_approval_emails_to.who"
                                             class="input-field"
                                             name="forward_careplan_approval_emails_to.who">
                                <option v-for="option in carePlanApprovalEmailOptions" :value="option.value"
                                        v-text="option.name"></option>
                            </material-select>
                        </div>

                        <div v-show="formData.forward_careplan_approval_emails_to.who !== 'billing_provider'"
                             class="input-field col s6">
                            <material-select v-model="formData.forward_careplan_approval_emails_to.user_id"
                                             class="input-field"
                                             name="forward_careplan_approval_emails_to.user_id">
                                <option v-for="user in staff" :value="user.id" v-if="user.id !== formData.id"
                                        v-text="user.full_name"></option>
                            </material-select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <material-select multiple v-model="formData.locations" name="locations" id="locations"
                                         :class="isValid(formData.locations)">
                            <option v-for="location in locations" :value="location.id"
                                    v-text="location.name"></option>
                        </material-select>

                        <label for="locations">Locations</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12">
                        <div @click="submitForm()"
                             class="btn green waves-effect waves-light right">
                            Save & Close
                        </div>

                        <div @click="close()"
                             class="btn red waves-effect waves-light right"
                             style="margin-right: 2rem;">
                            Close
                        </div>
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
    import {errors, practiceStaff, practiceLocations} from '../../../store/getters'
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
            //select all locations if this is a new staff member
            if (this.formData.id === 'new') {
                this.formData.locations = this.locations.map((loc) => {
                    return loc.id
                })
            }
        },

        computed: Object.assign(
            mapGetters({
                errors: 'errors',
                staff: 'practiceStaff',
                locations: 'practiceLocations'
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
                                Materialize.toast(this.formData.first_name + ' ' + this.formData.last_name + ' was successfully updated.', 3000)
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
                    'id': 'new',
                    'practice_id': $('meta[name=practice-id]').attr('content'),
                    'email': '',
                    'last_name': '',
                    'first_name': '',
                    'full_name': '',
                    'phone_number': '',
                    'phone_extension': '',
                    'phone_type': 1,
                    'grantAdminRights': '',
                    'sendBillingReports': '',
                    'role': {},
                    'role_name': 'med_assistant',
                    'locations': [],
                    'emr_direct_address': '',
                    'forward_alerts_to': {
                        'who': 'billing_provider',
                        'user_id': '',
                    },
                    'forward_careplan_approval_emails_to': {
                        'who': 'billing_provider',
                        'user_id': '',
                    },
                },
                formState: {},
                roleOptions: [{
                    text: 'Medical Assistant',
                    id: 'med_assistant'
                }, {
                    text: 'Office Admin',
                    id: 'office_admin'
                }, {
                    text: 'Provider',
                    id: 'provider'
                }, {
                    text: 'Registered Nurse',
                    id: 'registered-nurse'
                }, {
                    text: 'Specialist',
                    id: 'specialist'
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
                ],
                contactOptions: [
                    {
                        name: 'Provider',
                        value: 'billing_provider',
                    },
                    {
                        name: 'Someone else in addition to provider',
                        value: 'forward_alerts_in_addition_to_provider',
                    },
                    {
                        name: 'Someone else instead of provider',
                        value: 'forward_alerts_instead_of_provider',
                    },
                ],
                carePlanApprovalEmailOptions: [
                    {
                        name: 'Provider',
                        value: 'billing_provider',
                    },
                    {
                        name: 'Someone else in addition to provider',
                        value: 'forward_careplan_approval_emails_in_addition_to_provider',
                    },
                    {
                        name: 'Someone else instead of provider',
                        value: 'forward_careplan_approval_emails_instead_of_provider',
                    },
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
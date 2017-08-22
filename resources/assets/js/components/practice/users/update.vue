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

                },
                formState: {},
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
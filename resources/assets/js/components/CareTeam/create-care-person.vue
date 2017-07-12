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
    <modal v-if="form.show">
        <template slot="header">
            <h2>Create New User</h2>
            {{form}}
        </template>

        <template slot="body">
            BODY
        </template>

        <template slot="footer">
            <button class=" btn btn-default" @click="cancelForm">Cancel</button>
            <button :disabled="form.busy" @click="sendForm" class=" btn btn-info">
                Create User <i v-if="form.busy" class="fa fa-spinner fa-pulse fa-fw"></i>
            </button>
        </template>
    </modal>
</template>

<script>
    import modal from '../shared/modal.vue';
    import {mapGetters, mapActions} from 'vuex'
    import {cancelForm} from '../../store/actions'
    import {form} from '../../store/getters';

    export default {
        components: {
            modal
        },

        computed: Object.assign({},
            mapGetters({
                form: 'form'
            }),
            {}
        ),

        methods: Object.assign({},
            mapActions(['cancelForm']),
            {
                sendForm() {

                }
            }
        ),

        data()
        {
            return {
                id: '',
                formatted_type: 'External',
                alert: false,
                is_billing_provider: false,
                user: {
                    id: '',
                    email: '',
                    first_name: '',
                    last_name: '',
                    address: '',
                    address2: '',
                    city: '',
                    state: '',
                    zip: '',
                    phone_numbers: {
                        0: {
                            id: '',
                            number: '',
                        }
                    },
                    primary_practice: {
                        id: '',
                        display_name: ''
                    },
                    provider_info: {
                        id: '',
                        qualification: '',
                        specialty: '',
                    }
                },
            }
        }
        ,

    }
</script>
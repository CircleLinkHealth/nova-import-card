<template>
    <div>
        <p>
            <i v-on:click="createCarePerson" class="mini-action-button__icon material-icons">contact_mail</i>
        </p>
        <p class="mini-action-button__text--hide">Add Care Person</p>


        <component :is="currentModal" :model="editedUser"></component>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import { showForm } from '../store/actions'

    export default {
        data() {
            return {
                currentModal: '',
                editedUser: null,
                showModal: false
            }
        },

        mounted() {
            console.log('component-provider-ui')
        },

        methods: Object.assign(
            mapActions(['showForm']),
            {
                createCarePerson() {
                    this.editedUser = {}
                    this.currentModal = 'create-care-person'
                    this.showForm(true)
                }
            }
        )
    }
</script>
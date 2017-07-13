<template>
    <div>
        <label for="provider">
            Select Existing Provider (or, <span style="color: #4fb2e2"><a
                href="#"
                v-on:click="createCarePerson">add new</a></span>)
        </label>

        <component :is="currentModal" :model="editedUser"></component>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'
    import {showForm} from "../../store/actions";


    export default {
        data() {
            return {
                currentModal: '',
                editedUser: null
            }
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
        ),
    }
</script>
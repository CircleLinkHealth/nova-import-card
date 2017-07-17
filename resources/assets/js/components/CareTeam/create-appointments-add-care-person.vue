<template>
    <div>
        <label for="provider">
            Select Existing Provider (or, <span style="color: #4fb2e2"><a
                href="#"
                v-on:click="createCarePerson">add new</a></span>)
        </label>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'
    import {setOpenModal} from "../../store/actions";


    export default {
        methods: Object.assign({},
            mapActions(['setOpenModal']),
            {
                createCarePerson() {
                    this.setOpenModal({
                        name: 'create-care-person'
                    })
                }
            }
        ),
    }
</script>
<script>
    import {mapActions} from 'vuex'
    import {addNotification} from '../../../store/actions'
    export default {
        props: ['day', 'hours'],

        mounted() {
            if (this.hours) {
                this.workHours = JSON.parse(this.hours)
            }
        },

        data() {
            return {
                workHours: {},
                edited: false,
                beforeEditCache: null,
                min: 1,
                max: 12
            }
        },

        methods: Object.assign(mapActions(['addNotification']), {
            edit() {
                this.beforeEditCache = this.workHours[this.day]
                this.edited = true
            },

            doneEdit() {
                this.edited = false

                if (this.workHours[this.day] > 12 || this.workHours[this.day] < 1) {
                    this.workHours[this.day] = this.beforeEditCache

                    this.addNotification({
                        title: "Invalid number of work hours.",
                        text: "Enter a number between 1 and 12.",
                        type: "danger",
                        timeout: true
                    })
                }

            },

            cancelEdit() {
                this.workHours[this.day] = this.beforeEditCache
                this.edited = false
            },
        })
    }
</script>

<template>
    <div v-if="workHours[day]">
        <label v-if="!edited" @click="edit()" class="inline-edit-label">{{workHours[day]}} hrs</label>

        <input v-if="edited" type="number" :min="min" :max="max"
               v-model="workHours[day]"
               @blur="doneEdit()"
               @keyup.enter="doneEdit()"
               @keyup.esc="cancelEdit()">
    </div>
</template>

<style>
    .inline-edit-label {
        border: 1px solid #ccc;
        padding: 1px;
    }
</style>
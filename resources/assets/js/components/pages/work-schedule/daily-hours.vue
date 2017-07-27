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
                }x
            },

            cancelEdit() {
                this.workHours[this.day] = this.beforeEditCache
                this.edited = false
            },
        })
    }
</script>

<template>
    <div>
        <div>
            <div class="view">
                <label v-if="!edited" @dblclick="edit()">{{workHours[day]}}</label>
            </div>
            <input v-if="edited" type="number" :min="min" :max="max"
                   v-model="workHours[day]"
                   v-todo-focus="todo == edited"
                   @blur="doneEdit()"
                   @keyup.enter="doneEdit()"
                   @keyup.esc="cancelEdit()">
        </div>
    </div>
</template>
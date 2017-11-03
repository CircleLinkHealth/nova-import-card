<template>
    <div :class="{ className: className }">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <input type="date" v-model="date" required />
                <span @click="toggleEdit">&#9989;</span>
            </form>
        </div>
        <div v-if="!isEditMode" @dblclick="toggleEdit">
            {{text}}
        </div>
    </div>
</template>

<script>
    import moment from 'moment'

    const INPUT_DATE_FORMAT = 'YYYY-mm-DD'

    export default {
        name: 'TextEditable',
        props: ['value', 'format', 'is-edit', 'class-name'],
        data(){
            return {
                date: moment(this.value, this.format).format(INPUT_DATE_FORMAT),
                isEditMode: this.isEdit
            }
        },
        computed: {
            text() {
                return this.moment.format(this.format)
            },
            moment() {
                return moment(this.date, INPUT_DATE_FORMAT)
            }
        },
        methods: {
            toggleEdit(e) {
                e.preventDefault();
                this.isEditMode = !this.isEditMode;
            }
        }
    }
</script>

<style>
    
</style>
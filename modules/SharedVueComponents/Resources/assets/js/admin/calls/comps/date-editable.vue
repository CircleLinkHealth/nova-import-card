<template>
    <div :class="className">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <input type="date" class="float-left" v-model="date" required/>
                <span class="float-right" @click="toggleEdit">&#9989;</span>
            </form>
        </div>
        <div v-if="!isEditMode" @dblclick="toggleEdit">
            {{text}}
        </div>
    </div>
</template>

<script>
    /**
     * The date-editable component is used to edit dates
     *
     * Input:
     * value: The Date as a string, or Date object
     * format: The Format of the Date above if it is a string e.g. DD-mm-YYYY
     * is-edit: A boolean indicating whether or not the component is in EDIT mode
     * class-name: A string containing class names to pass to the component DIV
     * on-change: To contain a reference to a function that the date value will be passed to when changed
     *
     * EDIT: pangratios
     * I added a prevValue property in data.
     * I could not make the component update from outside.
     * eg. Passing as v-model="call['Next Call']" and then
     *     updating call["Next Call"] would not update this component.
     * So, I created a revertCallback for my needs.
     * Basically, if there is an error after changing the value,
     * I needed a way to revert the data shown from this component.
     */

    import moment from 'moment';

    const INPUT_DATE_FORMAT = 'YYYY-mm-DD';

    const defaultConfirmMessage = 'Are you sure?';

    export default {
        name: 'DateEditable',
        props: [
            'value',
            'format',
            'is-edit',
            'class-name',
            'on-change',
            'show-confirm',
            'confirm-message'
        ],
        data() {
            return {
                prevValue: moment(this.value, this.format).format(INPUT_DATE_FORMAT),
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

                //when switching to edit mode, we want to store the original value
                if (!this.isEditMode) {
                    this.prevValue = this.text;
                }

                if (!this.isEditMode && this.showConfirm && !confirm(this.confirmMessage || defaultConfirmMessage)) {
                    return;
                }

                this.isEditMode = !this.isEditMode;
                if (!this.isEditMode && typeof(this.onChange) === 'function') {
                    /**this.onChange is a function to be passed in as a prop */
                    this.onChange(this.text, this.moment, this.prevValue, this.revertCallback)
                }
            },
            revertCallback() {
                this.date = this.prevValue;
            }

        },
        watch: {
            value(newVal, oldVal) {
                this.date = moment(newVal, this.format).format(INPUT_DATE_FORMAT)
            }
        }
    }
</script>

<style>
    .float-left {
        float: left;
        width: 90%;
    }

    .float-right {
        float: right;
        margin-top: 4px;
    }

    span.float-right {
        cursor: pointer;
    }
</style>
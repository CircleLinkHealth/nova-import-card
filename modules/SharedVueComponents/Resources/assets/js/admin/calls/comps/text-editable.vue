<template>
    <div :class="className">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <textarea class="float-left form-control" v-if="multi" v-model="text" required></textarea>
                <input type="text" class="float-left" v-if="!multi" v-model="text" required />
                <button class="float-right icon-btn" v-if="!noButton" type="submit">&#9989;</button>
            </form>
        </div>
        <div v-if="!isEditMode" @dblclick="toggleEdit">
            {{text || 'Edit'}}
        </div>
    </div>
</template>

<script>
    /**
     * The text-editable component is used to edit text
     * 
     * Input:
     * value: A string value
     * is-edit: A boolean indicating whether or not the component is in EDIT mode
     * class-name: A string containing class names to pass to the component DIV
     * on-change: To contain a reference to a function that the text value will be passed to when changed
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

    export default {
        name: 'TextEditable',
        props: ['value', 'is-edit', 'class-name', 'on-change', 'multi', 'no-button'],
        data(){
            return {
                prevValue: this.value,
                text: this.value,
                isEditMode: this.isEdit
            }
        },
        methods: {
            toggleEdit(e) {
                e.preventDefault();

                //when switching to edit mode, we want to store the original value
                if (!this.isEditMode) {
                    this.prevValue = this.value;
                }

                this.isEditMode = !this.isEditMode;
                if (!this.isEditMode && typeof(this.onChange) === 'function') {
                    this.onChange(this.text, this.prevValue, this.revertCallback)
                }
            },
            revertCallback() {
                this.text = this.prevValue;
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

    .icon-btn {
        border: none;
        background: none;
    }
</style>
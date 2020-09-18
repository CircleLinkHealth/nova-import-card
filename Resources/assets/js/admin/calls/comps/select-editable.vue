<template>
    <div :class="className">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <select v-model="text" class="float-left" @change="onSelectChange">
                    <option v-if="v.constructor.name === 'String'" v-for="(v, index) in values" :key="index" :value="v">
                        {{v}}
                    </option>
                    <option v-if="v.constructor.name === 'Object'" v-for="(v, index) in values" :key="index"
                            :value="v.value">{{v.text}}
                    </option>
                </select>
                <span class="float-right" @click="toggleEdit" v-if="!noButton">&#9989;</span>
            </form>
        </div>
        <div v-if="!isEditMode" @dblclick="toggleEdit">
            {{frontText || displayText || (text || {}).text || text || 'unassigned'}}
        </div>
    </div>
</template>

<script>
    /**
     * The select-editable component is used to edit dates
     *
     * Input:
     * values: The string or object values as an array e.g. ['a', 'b'] or [{ text: 'a', value: 'b' }]
     * value: The initial value
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

    export default {
        name: 'SelectEditable',
        props: ['value', 'values', 'is-edit', 'class-name', 'on-change', 'no-button', 'display-text'],
        data() {
            return {
                prevValue: this.value || null,
                text: this.value || null,
                isEditMode: this.isEdit
            }
        },
        computed: {
            frontText() {
                const ret = (this.values.find(item => (item && ((item.constructor.name === 'Object' && item.value) || item)) == this.text) || '')
                return (ret || {}).text || ret
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
                    this.onChange(this.text, this.prevValue, this.revertCallback);
                    this.$emit('change', {target: this.$el.querySelector('select'), value: this.text})
                }
            },
            onSelectChange(e) {
                if (this.noButton && this.text) {
                    this.toggleEdit(e);
                }
            }
            ,
            revertCallback() {
                this.text = this.prevValue;
            }
        },
        watch: {
            value(newValue) {
                this.text = this.value || null
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
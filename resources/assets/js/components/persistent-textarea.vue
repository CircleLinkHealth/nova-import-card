<template>
    <textarea v-model="text" :id="id" :class="className" :rows="rows" :cols="cols" 
            :placeholder="placeholder" :name="name" :required="required" @change="changeTextArea"></textarea>
</template>

<script>
    import { sstor } from '../stor'

    export default {
        name: 'persistent-textarea',
        data() {
            return {
                text: this.value
            }
        },
        props: {
            id: String,
            className: String,
            name: String,
            value: String,
            storageKey: {
                type: String,
                required: true
            },
            required: Boolean,
            rows: {
                type: Number,
                defaultValue: 10
            },
            cols: {
                type: Number,
                defaultValue: 100
            },
            placeholder: String
        },
        methods: {
            changeTextArea() {
                sstor.add(this.storageKey, this.text)
                this.$emit('input', this.text)
            }
        },
        mounted() {
            if (this.value && this.value.length) {
                sstor.remove(this.storageKey);
                this.changeTextArea();
            }
            else {
                this.text = sstor.get(this.storageKey)
            }
        }
    }
</script>

<style>
    
</style>
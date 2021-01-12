<template>
    <span class="error-btn" v-if="errors()[name]" 
                        title="view error message"
                        @click="showErrorModal()">x</span>
</template>

<script>
    import { Event } from 'vue-tables-2'

    export default {
        name: 'error-modal-button',
        props: {
            errors: {
                type: Function,
                required: true
            },
            name: {
                type: String,
                required: true
            }
        },
        methods: {
            showErrorModal() {
                Event.$emit('modal-error:show', { body: this.errors()[this.name] }, () => {
                    this.errors()[this.name] = null
                })
            }
        }
    }
</script>

<style>
    .error-btn {
        display: inline-block;
        width: 15px;
        height: 15px;
        background-color: white;
        text-align: center;
        padding-top: 0px;
        border-radius: 8px;
        color: red;
        font-size: 10px;
        cursor: pointer;
        border: 1px solid red;
        padding-bottom: 14px;
        position: relative;
        left: 11px;
        top: 5px;
    }
</style>
<template>
    <div :class="{ className: className }">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <select></select>
                <span @click="toggleEdit">&#9989;</span>
            </form>
        </div>
        <div v-if="!isEditMode" @dblclick="toggleEdit">
            {{text}}
        </div>
    </div>
</template>

<script>
    export default {
        name: 'TextEditable',
        props: ['value', 'values', 'is-edit', 'class-name'],
        data(){
            return {
                text: this.value,
                textValues: this.values || [],
                isEditMode: this.isEdit
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
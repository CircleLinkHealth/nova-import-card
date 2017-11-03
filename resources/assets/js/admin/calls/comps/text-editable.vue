<template>
    <div :class="{ className: className }">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <input type="text" v-model="text" required />
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
        props: ['value', 'is-edit', 'class-name'],
        data(){
            return {
                text: this.value,
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
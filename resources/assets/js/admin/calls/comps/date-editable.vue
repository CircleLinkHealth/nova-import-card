<template>
    <div :class="{ className: className }">
        <div v-if="isEditMode">
            <form @submit="toggleEdit">
                <input type="date" v-model="date" required />
                <span @click="toggleEdit">&#9989;</span>
            </form>
        </div>
        <div v-if="!isEditMode" @dblclick="toggleEdit">
            {{date}}
        </div>
    </div>
</template>

<script>
    export default {
        name: 'TextEditable',
        props: ['value', 'format', 'is-edit', 'class-name'],
        data(){
            return {
                date: new Date(this.value),
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
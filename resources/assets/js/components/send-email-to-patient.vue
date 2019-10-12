<template>
    <div>
        <div><h3>Compose new mail</h3></div>
        <div v-if="this.patient.email">Email: {{this.patient.email}}</div>
        <div v-else>
            <textarea name="custom-patient-email" placeholder="Enter email..."></textarea>
            <input type="checkbox" id="default-patient-email" name="default-patient-email" value="1">
            <label for="default-patient-email"><span> </span>Save as default patient email</label>
        </div>
        <div>
            <form>
                <VueTrix inputId="editor1" v-model="editorContent" placeholder="enter your content..."/>
            </form>
        </div>
    </div>
    
</template>

<script>
    import VueTrix from "vue-trix";

    export default {
        name: "send-email-to-patient",
        props: {
            patient: {
                type: Object,
                required: true
            }
        },
        components: {
            VueTrix
        }
    }
</script>

<style scoped>

</style>
<template>
    <div style="margin-bottom: 20px">
        <div class="form-group">
            <i class="far fa-envelope"  style="margin-right: 8px"></i>
            <label for="patient-email-body">
                Compose Mail
            </label></div>
        <div class="form-group" v-if="this.patient.email">To: {{this.patient.email}}</div>
        <div  class="form-group" v-else>
            <span>Patient email not found. Please input below:</span><br>
            <textarea name="custom-patient-email" placeholder="Enter email..."></textarea><br>
            <input type="checkbox" id="default-patient-email" name="default-patient-email" value="1">
            <label for="default-patient-email"><span> </span>Save as default patient email</label>
        </div>
        <div class="form-group">
                <VueTrix id="patient-email-body" inputId="patient-email-body-input"
                         inputName="patient-email-body"
                         v-model="editorContent"
                         placeholder="Enter your content... (please do not enter patient PHI)"
                         @trix-file-accept="handleFile"
                         @trix-attachment-add="handleAttachmentAdd"
                         @trix-attachment-remove="handleAttachmentRemove"
                         @trix-focus="handleEditorFocus"
                         @trix-blur="handleEditorBlur"/>
        </div>
    </div>
    
</template>

<script>
    import VueTrix from "vue-trix";
    import {rootUrl} from '../app.config.js';
    import {Event} from 'vue-tables-2';
    import {eventToggleSendMailToPatient} from '../notes-patient-email-events';

    export default {
        name: "send-email-to-patient",
        props: {
            patient: {
                type: Object,
                required: true
            }
        },
        data () {
            return {
                editorContent : '',
                attachments: []
            }

        },
        components: {
            VueTrix
        },
        computed: {
            uploadUrl() {
                //create another url maybe another controller
                return rootUrl('/patient-email-attachment/' + this.patient.id + '/upload');
            },
        },
        methods: {
            handleFile (file) {
                // console.log('Drop file:', file)
            },
            handleAttachmentAdd (event) {
                //this is to upload as soon as file is selected via ajax
                let formData = new FormData();
                let file = event.attachment.file;
                formData.append("file", file);
                return this.axios.post(this.uploadUrl, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then((response, status) => {
                    if (response) {
                        this.attachments.push({
                            'name': response.data['name'],
                            'path': response.data['path']
                        });
                        App.$emit('file-upload', this.attachments);
                    }
                    else {
                        throw new Error('no response')
                    }
                    return null
                }).catch(err => {
                    throw new Error(err)
                })
            },
            handleAttachmentRemove (file) {
                //route to remove care document
            },
            handleEditorFocus (event) {
                //maybe tips - 'glepete ta PHI'
                // console.log('Editor is focused:', event)
            },
            handleEditorBlur (event) {
                // console.log('Editor is lost focus', event)
            }
        },
    }
</script>

<style scoped>
    .hidden {
        display: none;
    }
</style>
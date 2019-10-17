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
        <!--<div class="form-group">-->
            <!--<progress v-if="this.showProgressBar" class="progress-bar" max="100" :value.prop="uploadPercentage"></progress>-->
        <!--</div>-->
        <div class="form-group">
                <VueTrix class="" id="patient-email-body" inputId="patient-email-body-input"
                         localStorage
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
                attachments: [],
                // uploadPercentage: 0,
                // showProgressBar: false
            }

        },
        components: {
            VueTrix
        },
        computed: {
            uploadUrl() {
                return rootUrl('/patient-email-attachment/' + this.patient.id + '/upload');
            },
            deleteUrl() {
                return rootUrl('/patient-email-attachment/' + this.patient.id + '/delete');
            },
        },
        methods: {
            handleFile (file) {
            },
            handleAttachmentAdd (event) {
                //this is to upload as soon as file is selected via ajax
                let formData = new FormData();
                let file = event.attachment.file;
                formData.append("file", file);
                // this.showProgressBar = true;
                return this.axios.post(this.uploadUrl, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function( progressEvent ) {
                        var progress = parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                            // progressEvent.loaded / progressEvent.total * 100;
                        event.attachment.setUploadProgress(progress);
                            // parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                    }.bind(this),
                    load: function(loadEvent){
                        // var attributes = {
                        //     url: 'host' + 'key',
                        //     href: 'host' + 'key' + "?content-disposition=attachment"
                        // };
                        // event.attachments.setAttributes(attributes);
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
                    // this.uploadPercentage = 0;
                    // this.showProgressBar = false;
                    return null
                }).catch(err => {
                    throw new Error(err)
                })
            },
            handleAttachmentRemove (event) {
                let formData = new FormData();
                let file = event.attachment.file;
                formData.append("file", file);
                this.showProgressBar = true;
                return this.axios.post(this.deleteUrl, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function( progressEvent ) {
                        let progress = progressEvent.loaded / progressEvent.total * 100;
                        event.attachment.setUploadProgress(progress);
                        // parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                    }.bind(this),
                    load: function(loadEvent){
                        // var attributes = {
                        //     url: 'host' + 'key',
                        //     href: 'host' + 'key' + "?content-disposition=attachment"
                        // };
                        // event.attachments.setAttributes(attributes);
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
                    // this.uploadPercentage = 0;
                    // this.showProgressBar = false;
                    return null
                }).catch(err => {
                    throw new Error(err)
                })
            },
            handleEditorFocus (event) {
                //maybe tips - 'glepete ta PHI'
                // console.log('Editor is focused:', event)
            },
            handleEditorBlur (event) {
            }
        },
    }
</script>

<style scoped>
    .hidden {
        display: none;
    }

    .progress-bar {
        width: 50%;
        margin-left: 25%;
    }
</style>
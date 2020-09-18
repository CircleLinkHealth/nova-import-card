<template>
    <div style="margin-bottom: 20px">
        <div class="form-group">
            <i class="far fa-envelope" style="margin-right: 8px"></i>
            <label for="patient-email-body">
                Compose Mail
            </label></div>
        <div class="form-group" v-if="patientEmailIsValid">To: {{this.patientEmail}}</div>
        <div class="form-group" v-else>
            <div style="padding-bottom: 10px"><span><strong>Patient email not found or is invalid.</strong> Send to:</span><br></div>
            <div class="col-sm-4" style="padding-left: 0"><input class="form-control" type="email" id="custom-patient-email" name="custom-patient-email"
                                                                 placeholder="Enter email..."></div>
            <div class="col-sm-4"><input type="checkbox" id="default-patient-email" name="default-patient-email"
                                         value="1">
                <label style="padding-top: 5px;" for="default-patient-email"><span> </span>Save as default patient email</label></div>

        </div>
        <div class="form-group">
            <div v-if="this.errors.length > 0" class="alert alert-danger" >
                <ul>
                    <li style="list-style: disc !important" v-for="error in this.errors">{{error}}</li>
                </ul>
            </div>
            <div class="col-sm-6" style="padding-left: 0 !important">
                <input class="form-control" type="text" placeholder="Enter subject..." id="email-subject" name="email-subject" v-model="emailSubject">
            </div>
        </div>
        <div class="form-group">
            <VueTrix ref="patientEmail" id="patient-email-body" inputId="patient-email-body-input"
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
    import {rootUrl} from '../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config.js';

    export default {
        name: "send-email-to-patient",
        props: {
            patientId: {
                required: true
            },
            patientEmail: {
                required: true
            }
        },
        data() {
            return {
                editorContent: '',
                emailSubject: '',
                attachments: [],
                errors: '',
            }

        },
        components: {
            VueTrix
        },
        computed: {
            uploadUrl() {
                return rootUrl('/patient-email/' + this.patientId + '/upload-attachment');
            },
            deleteUrl() {
                return rootUrl('/patient-email/' + this.patientId + '/delete-attachment');
            },
            patientEmailIsValid(){
                if (! this.patientEmail || this.patientEmail.length === 0){
                    return false;
                }

                if (this.patientEmail.endsWith('@careplanmanager.com') ||  this.patientEmail.endsWith('@example.com') || this.patientEmail.endsWith('@noEmail.com')){
                    return false;
                }

                return true;
            }
        },
        methods: {
            handleFile(file) {
            },
            handleAttachmentAdd(event) {
                //temporary fix, to prevent attachment from hiding text
                this.$refs.patientEmail.$refs.trix.editor.insertString("  ");
                let formData = new FormData();
                let file = event.attachment.file;
                formData.append("file", file);
                return this.axios.post(this.uploadUrl, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function (progressEvent) {
                        var progress = parseInt(Math.round((progressEvent.loaded * 80) / progressEvent.total));
                        // progressEvent.loaded / progressEvent.total * 100;
                        event.attachment.setUploadProgress(progress);
                        // parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                    }.bind(this),
                    onDownloadProgress: function (loadEvent) {
                        //this adds inline attachments using <a> and <img> tags. There is an issue with embedding them to the mailable, may fix in the future
                       // event.attachment.setAttributes({
                       //     url : JSON.parse(loadEvent.currentTarget.response).url,
                       //     href : JSON.parse(loadEvent.currentTarget.response).url
                       // });
                    }.bind(this)
                }).then((response, status) => {
                    if (response) {
                        event.attachment.setUploadProgress(100);
                        this.attachments.push({
                            'media_id': response.data['media_id'],
                            'path': response.data['path'],
                            'name': response.data['name']
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
            handleAttachmentRemove(event) {
                let formData = new FormData();
                let file = event.attachment.file;

                this.attachments = this.attachments.filter(function(a){
                    return a.name !== file.name;
                })
                formData.append("file", file);

                return this.axios.post(this.deleteUrl, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                }).then((response, status) => {
                    if (response) {
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
            handleEditorFocus(event) {
            },
            handleEditorBlur(event) {
            }
        },
        mounted(){
            App.$on('patient-email-body-errors', (errors) => {
               this.errors = errors;
            });

        }
    }
</script>

<style>
    .trix-content > ul > li {
        list-style: outside !important;
    }

    .trix-content > ol > li {
        list-style: decimal !important;
    }
</style>

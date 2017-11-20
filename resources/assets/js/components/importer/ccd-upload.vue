<template>
    <div>
        <vue-dropzone ref="vueDropzone" id="dropzone" param-name="file" 
            :url="dzOptions.url" 
            :headers="dzOptions.headers" 
            :upload-multiple="true"
            :accepted-file-types="dzOptions.acceptedFileTypes" />
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import { rootUrl, csrfToken } from '../../app.config'
    import CcdRegisterEvents from './ccd-upload.event'

    export default {
        name: 'ccd-upload',
        components: {
            vueDropzone: vue2Dropzone
        },
        data() {
            return {
                dzOptions: {
                    url: rootUrl('ccd-importer/imported-medical-records'),
                    headers: { 
                        'X-CSRF-TOKEN': csrfToken()
                    },
                    acceptedFileTypes: 'text/xml,application/xml,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }
            }
        },
        mounted() {
            console.log(this.$refs.vueDropzone)

            CcdRegisterEvents(this, this.$refs.vueDropzone)
        }
    }
</script>

<style>
    
</style>
<template>
    <div>
        <vue-dropzone ref="vueDropzone" id="dropzone" :url="dropzoneOptions.url" :options="dropzoneOptions" />
    </div>
</template>

<script>
    import vue2Dropzone from 'vue2-dropzone'
    import { rootUrl } from '../../app.config'

    export default {
        name: 'ccd-upload',
        components: {
            vueDropzone: vue2Dropzone
        },
        data() {
            return {
                dropzoneOptions: {
                    url: rootUrl('ccd-importer/post'),
                    headers: {  },
                    acceptedFiles: 'application/xml,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }
            }
        }
    }
</script>

<style>
    
</style>
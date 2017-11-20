<template>
    <div>
        <vue-dropzone ref="vueDropzone" id="dropzone" :url="dzOptions.url" :accepted-file-types="dzOptions.acceptedFileTypes" />
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
                dzOptions: {
                    url: rootUrl('ccd-importer/post'),
                    headers: {  },
                    acceptedFileTypes: 'application/xml,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }
            }
        },
        mounted() {
            console.log(this.$refs.vueDropzone)
        }
    }
</script>

<style>
    
</style>
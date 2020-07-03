<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h4 class="text-center text-3xl text-80 font-light">Invoices Download</h4>
        </div>

            <div class="dropdown">
                <vue-select name="practices"
                            id="practices"
                            multiple
                            v-model="practicesSelected"
                            :options="practices">
                </vue-select>
            </div>

        <br>

        <div class="button">
            <a class="btn btn-default btn-primary ml-auto mt-auto"
               style="cursor: pointer; background-color: #4baf50" @click="downloadInvoices()">Download Invoices</a>
        </div>

        <br>

        <loader v-if="loading" width="30"></loader>

        <div class="flex">
            <div v-if="errors">
                <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
            </div>
        </div>
    </card>
</template>

<script>
    import VueSelect from 'vue-select';
export default {
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],

    components: {
        'vue-select': VueSelect,
    },

    data() {
        return {
            practicesSelected:[],
            practices:[],
            loading:false,
            errors:null
        };
    },

    methods:{
        downloadInvoices(){
            this.loading = true;
            Nova.request().post('/nova-vendor/invoices-download/download', {
                practices:this.practicesSelected,
                downloadFormat:'csv',
                date:''
            }).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        },
    },

    mounted() {
        this.loading = true;
        Nova.request().get('/nova-vendor/invoices-download/dropdown-practices').then(response => {
                this.practices = response.data
                this.loading = false;
            }).catch(error => {
                console.log(error);
            });
    },
}
</script>
<style>
    #practices > div{
        min-width: 180px;
    }
</style>

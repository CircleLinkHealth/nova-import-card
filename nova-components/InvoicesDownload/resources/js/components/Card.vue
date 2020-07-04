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
        <div class="dropdown">
            <vue-select name="months"
                        id="months"
                        v-model="monthSelected"
                        :options="months">
            </vue-select>
        </div>

        <div class="dropdown">
            <vue-select name="downloadFormat"
                        id="downloadFormat"
                        multiple
                        v-model="formatsSelected"
                        :options="downloadFormats">
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

    const limitDate = '2020-05-01';
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
            errors:null,
            months:[],
            monthSelected:[],
            formatsSelected:[],
            downloadFormats:[
                {
                    label:'CSV',
                    value:'csv'
                },

                {
                    label:'PDF',
                    value:'pdf'
                }

            ]
        };
    },

    methods:{
        setPracticesForDropdown(){
            this.loading = true;
            Nova.request().get('/nova-vendor/invoices-download/dropdown-practices').then(response => {
                this.practices = response.data
                this.loading = false;
            }).catch(error => {
                console.log(error);
            });
        },

        downloadInvoices(){
            this.loading = true;
            Nova.request().post('/nova-vendor/invoices-download/download', {
                practices:this.practicesSelected,
                downloadFormats:this.formatsSelected,
                date:this.monthSelected
            }).then(response => {
                console.log(response.data);

            }).catch(error => {
                console.log(error);
            });
        },

        setMonthsForDropdown() {
            let dateStart = moment(limitDate);
            let dateEnd = moment();

            while (dateEnd.diff(dateStart) >= 0) {
                this.months.push(
                    {
                        label:dateStart.format('MMM YYYY'),
                        value:dateStart
                    }
                );
                dateStart.add(1, 'months');
            }
        },
    },

    mounted() {
        this.setPracticesForDropdown();
        this.setMonthsForDropdown();
    },
}
</script>
<style>
    #practices > div{
        min-width: 180px;
    }

    #months > div{
        min-width: 180px;
    }
</style>

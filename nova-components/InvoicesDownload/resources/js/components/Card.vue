<template>
    <card class="flex flex-col items-center justify-center" style="display: inline-block;">
        <div class="px-3 py-3">
            <h4 class="text-center text-3xl text-80 font-light">Invoices Download</h4>
            <div class="py-2">
            <loader v-if="loading" width="30"></loader>

            <div style="display: inline-flex;">
                <div class="dropdown" style="padding-right: 10px;">
                    <vue-select name="months"
                                id="months"
                                placeholder="Select Month"
                                v-model="monthSelected"
                                :options="months">
                    </vue-select>
                </div>

                <div class="dropdown">
                    <vue-select name="downloadFormat"
                                id="downloadFormat"
                                placeholder="Download Format"
                                v-model="formatsSelected"
                                :options="downloadFormats">
                    </vue-select>
                </div>
            </div>
            <div class="button" style="text-align: center;">
                <a class="btn btn-default btn-primary ml-auto mt-auto"
                   style="cursor: pointer; background-color: #4baf50" @click="downloadInvoices()">Download Invoices</a>
            </div>

            <br>

            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
            </div>
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
        limiter(e) {
            if(e.length > 4) {
             alert('You can only select 4 Practices')
            }
        },

        downloadInvoices(){
            this.loading = true;
            Nova.request().post('/nova-vendor/invoices-download/download', {
                downloadFormats:this.formatsSelected,
                date:this.monthSelected
            }).then(response => {
                this.$toasted.success(response.data.message);
                this.loading = false;

            }).catch(error => {
                console.log(error);
                this.$toasted.error(error.response.data);
                this.loading = false;
            });
        },
        //This i better to be moved to BE. But can't npm run dev.
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
        this.setMonthsForDropdown();
    },
}
</script>
<style>
    #months > div{
        min-width: 180px;
    }

    #downloadFormat > div{
        min-width: 180px;
    }
</style>

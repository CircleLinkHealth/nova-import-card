<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <span class="text-center text-3sm text-80 font-light">Select Range</span>
            <div class="py-4">
<!--                    <span class="flex ">-->
<!--                        <label for="start">-->
<!--                            {{__('Start Date: ')}}-->
<!--                        </label>-->

<!--                       <input id="start"-->
<!--                              type="date"-->
<!--                              class="form-block"-->
<!--                              v-model="startDate">-->
<!--                    </span>-->

<!--                <span class="flex ">-->
<!--                        <label for="end">-->
<!--                            {{__('End Date: ')}}-->
<!--                        </label>-->

<!--                       <input id="end"-->
<!--                              type="date"-->
<!--                              class="form-block"-->
<!--                              v-model="endDate">-->

<!--                    </span>-->
            </div>
            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
                <a class="btn btn-default btn-primary ml-auto mt-auto" style="cursor: pointer;" @click="downloadInvoices()">Submit</a>
            </div>
        </div>
    </card>
</template>

<script>
export default {
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],

    data(){
      return {
          startDate:'',
          endDate:'',
      };
    },

    methods:{
        downloadInvoices(){
            this.loading = true;
            Nova.request().post('/nova-vendor/invoices-download/download', {
                downloadFormat:'',
                date:''
            }).then(response => {
                this.$toasted.success(response.data.message);
                this.loading = false;

            }).catch(error => {
                let msg = 'There has been an error.';
                if (error.response?.data?.message) {
                    msg = error.response.data.message;
                }
                this.$toasted.error(msg);
                this.loading = false;
            });
        },
    },

    mounted() {
        //
    },
}
</script>

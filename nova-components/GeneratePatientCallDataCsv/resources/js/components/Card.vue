<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h1 class="text-xl font-light">Generate Patient Call Data Csv</h1>
            <form @submit.prevent="generateCsv" ref="form">
                <div class="py-4">
                    <span class="flex ">
                        <label for="months">
                            {{__('Select month: ')}}
                        </label>
                        <select name="months" id="months" v-model="month" style="margin-left: 10px">
                            <option v-for="month in this.months" v-bind:value="month">{{month}}</option>
                        </select>
                    </span>
                    <span class="text-gray-50">
                        {{ currentLabel }}
                    </span>

                </div>

                <div class="flex">
                    <div v-if="errors">
                        <p class="text-danger mb-1" v-for="error in errors">{{error[0]}}</p>
                    </div>
                    <button
                            :disabled="working"
                            type="submit"
                            class="btn btn-default btn-primary ml-auto mt-auto"
                    >
                        <loader v-if="working" width="30"></loader>
                        <span v-else>{{__('Generate CSV')}}</span>
                    </button>
                </div>
            </form>
        </div>
    </card>
</template>

<script>
    import moment from 'moment';

export default {
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],
    data() {
        return {
            label: 'test label',
            working: false,
            errors: null,
            month : null,
            months : []
        };
    },
    methods: {
        setMonthsForDropdown() {
            let dateStart = moment().subtract(10, 'months');
            let dateEnd = moment();

            while (dateEnd.diff(dateStart, 'months') >= 0) {
                this.months.push(dateStart.format('MMM YYYY'));
                dateStart.add(1, 'months');
            }
        },
        generateCsv() {
            this.working = true;
            let formData = new FormData();
            formData.append('month', this.month);
            Nova.request()
                .post(
                    '/nova-vendor/generate-patient-call-data-csv/generate-csv-for-month/',
                    formData
                )
                .then(({ data }) => {
                    this.$toasted.success(data.message);
                    this.errors = null;
                })
                .catch(({ response }) => {
                    if (response.data.danger) {
                        this.$toasted.error(response.data.danger);
                        this.errors = null;
                    } else {
                        this.errors = response.data.errors;
                    }
                })
                .finally(() => {
                    this.working = false;
                    this.$refs.form.reset();
                });
        },
    },

    mounted() {
        this.setMonthsForDropdown();
    },
}
</script>

<template>
    <card class="flex flex-col h-auto">
        <div class="px-3 py-3">
            <h2 class="text-xl font-light">Generate Patient/Nurse Call Data Sheet</h2>
            <form @submit.prevent="" ref="form">
                <div class="py-4">
                    <span class="flex ">
                        <label for="months">
                            {{__('Select month: ')}}
                        </label>
                        <select name="months" id="months" v-model="month" style="margin-left: 10px">
                            <option v-for="month in this.months" v-bind:value="month">{{month}}</option>
                        </select>
                    </span>
                </div>

                <div class="flex">
                    <div v-if="errors">
                        <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                    </div>
                        <a class="btn btn-default btn-primary ml-auto mt-auto" style="cursor: pointer;" @click="generateCsv">Generate Sheet</a>
                </div>
            </form>
        </div>
    </card>
</template>

<script>
    import moment from 'moment';
    import {rootUrl} from '../rootUrl.js'

export default {
    props: [
        'card',
    ],
    data() {
        return {
            errors : null,
            month : null,
            months : []
        };
    },
    methods: {
        setMonthsForDropdown() {
            let dateStart = moment().subtract(10, 'months');
            let dateEnd = moment();

            while (dateEnd.diff(dateStart) >= 0) {
                this.months.push(dateStart.format('MMM YYYY'));
                dateStart.add(1, 'months');
            }
        },
        generateCsv() {
            if (! this.month){
                this.errors = ['Please select a month'];
                return;
            }
            const url = rootUrl(`/nova-vendor/generate-patient-call-data-csv/generate-csv-for-month/${this.month}`);
            document.location.href = url;
        },
    },

    mounted() {
        this.setMonthsForDropdown();
    },
}
</script>

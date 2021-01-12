<template>
    <card class="flex flex-col items-center justify-center">
        <div class="px-3 py-3">
            <h1 class="text-center text-3xl text-80 font-light">Generate Success Stories Report</h1>
            <div class="py-4">
                    <span class="flex ">
                        <label for="months">
                            {{__('Select month: ')}}
                        </label>
                        <select name="months"
                                id="months"
                                v-model="month"
                                style="margin-left: 10px">
                            <option v-for="month in this.months"
                                    v-bind:value="month">
                                {{month}}
                            </option>
                        </select>
                    </span>
            </div>
            <div class="flex">
                <div v-if="errors">
                    <p class="text-danger mb-1" v-for="error in errors">{{error}}</p>
                </div>
                <a class="btn btn-default btn-primary ml-auto mt-auto" style="cursor: pointer;" @click="generateCsv">Generate
                    Sheet</a>
            </div>
        </div>
    </card>
</template>

<script>
    import moment from "moment";
    import {rootUrl} from "../../../../GeneratePatientCallDataCsv/resources/js/rootUrl";
    const startLimitDate = new Date('2020-04-01');

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
                errors: null,
                month: null,
                months: []
            };
        },

        methods: {
            setMonthsForDropdown() {
                let dateStart = moment(startLimitDate);
                let dateEnd = moment();

                while (dateEnd.diff(dateStart) >= 0) {
                    this.months.push(dateStart.format('MMM YYYY'));
                    dateStart.add(1, 'months');
                }
            },

            generateCsv() {
                if (!this.month) {
                    this.errors = ['Please select a month'];
                    return;
                }
                const url = rootUrl(`/nova-vendor/generate-success-stories-report/generate-success-stories-report/${this.month}`);
                document.location.href = url;
            },
        },

        mounted() {
            this.setMonthsForDropdown();
        },
    }
</script>

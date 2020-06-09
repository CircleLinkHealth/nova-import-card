<template>
    <div>
        <div class="modal fade" id="dailyReport" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" style="position: fixed; margin-left: 134px;">
                <div class="modal-content" style="width: fit-content; overflow-y: scroll; max-height: 725px;">
                    <div class="modal-header">

                        <div class="modal-title" id="exampleModalLabel">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">
                                    <h3>Daily Report for: {{this.reportDate}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container">
                        <br>
                        <p>Dear {{this.reportData.nurse_full_name}},</p>
                        <p>&nbsp;</p>
                        <p>Thanks for providing care on the CircleLink platform on {{this.reportDate}}</p>
                        <p>&nbsp;</p>
                        <ol>
                            <li><strong>Work Completed Yesterday {{this.reportDate}}</strong></li>
                        </ol>
                        <ul>

                            <li>{{this.reportData.actualHours}} Hours Worked out of {{this.reportData.committedHours}}
                                Hours Committed
                            </li>
                            <li>Total calls completed: {{this.reportData.actualCalls}}</li>
                            <li>Total successful calls: {{this.reportData.successful}}</li>
                        </ul>
                        <p>&nbsp;</p>
                        <ol start="2">

                            <li><strong>Monthly Case Completion ({{this.reportData.caseLoadComplete}})</strong></li>
                        </ol>
                        <ul>
                            <li>Monthly caseload: {{this.reportData.totalPatientsInCaseLoad}} patients</li>
                            <li v-if="reportFlags.patientsCompletedRemaining">Patients completed for the month:
                                {{this.reportData.completedPatients}}
                            </li>
                            <li v-if="reportFlags.patientsCompletedRemaining">Patients remaining:
                                {{this.reportData.incompletePatients}}
                            </li>
                        </ul>
                        <br>
                        <div v-if="reportFlags.showEfficiencyMetrics">
                            <p>&nbsp;</p>
                            <ol start="3">
                                <li><strong>Efficiency Metrics</strong></li>
                            </ol>
                            <ul>
                                <li>Average CCM time per successful patient: {{this.reportData.avgCCMTimePerPatient}}
                                    minutes (goal is to
                                    stay as close to
                                    20 minutes as possible)
                                </li>
                                <ul>
                                    <li>Calculated by dividing your total CCM time on successful patients by the total
                                        amount of completed
                                        patients for the month
                                    </li>
                                </ul>
                                <li>Average time to complete a patient: {{this.reportData.avgCompletionTime}} minutes
                                    (goal is to be under
                                    30 minutes)
                                </li>
                                <ul>
                                    <li>Calculated by dividing your total CPM time by the number of completed patients
                                        for
                                        the month
                                    </li>
                                </ul>
                            </ul>
                            <p>&nbsp;</p>
                        </div>

                        <div v-if="reportFlags.enableDailyReportMetrics">
                            <ol start="4">
                                <li><strong>Scheduling and Monthly Hours</strong></li>
                            </ol>
                            <ul>
                                <li>Estimated time to complete case load: {{this.reportData.caseLoadNeededToComplete}}
                                    hrs
                                </li>
                                <ul>
                                    <li>Calculated by multiplying the average time to complete a patient (above) by
                                        total
                                        remaining patients
                                        and dividing by 60 minutes to get an hour total
                                    </li>
                                </ul>
                                <li>Committed hours for remainder of month:
                                    {{this.reportData.hoursCommittedRestOfMonth}} hrs
                                </li>
                                <ul>
                                    <li>For more accuracy, enter your schedule for the entire month. Otherwise, the
                                        system
                                        estimates based
                                        off the current week's hours
                                    </li>
                                </ul>
                                <li>Surplus or deficit for the remainder of month: <a
                                        :style="surplusDeficitColor">{{this.reportData.surplusShortfallHours}}</a> hr
                                    {{this.reportData.deficitOrSurplusText}}
                                </li>
                                <ul>
                                    <li><a style="color: green">Surplus</a> indicates you are doing well for the month
                                        and
                                        are on pace to
                                        successfully complete your caseload
                                    </li>
                                    <li><a style="color: red">Deficit</a> indicates you are behind in completing your
                                        caseload and need to
                                        make up hours or reach out for assistance in completing your caseload
                                    </li>
                                </ul>
                                <li>Next scheduled shift: {{this.reportData.totalHours}} hours between
                                    {{this.reportData.windowStart}} and
                                    {{this.reportData.windowEnd}}
                                    on {{this.reportData.nextUpcomingWindowDay}},
                                    {{this.reportData.nextUpcomingWindowMonth}}
                                </li>
                            </ul>
                        </div>

                        <p>If you have any questions, concerns or schedule changes, please reach out to your CLH
                            managers over Slack.</p>
                        <p>Have a great day and keep up the good work!</p>
                        <p>The CircleLink Health Team</p>
                    </div>


                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-primary"
                                style="float: right; background-color:#d9534f;"
                                @click="closeModal">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CalendarDailyReport",
        props: ['reportData', 'reportDate', 'reportFlags', 'popUpNow'],
        components: {

        },

        computed: {
            surplusDeficitColor() {
                {
                    return {
                        "color": this.reportData.deficitTextColor
                    }
                }
            },
        },

        methods: {
            closeModal() {
                $("#dailyReport").modal('toggle');
            },
        },

        mounted() {
            if (this.popUpNow){
                $("#dailyReport").modal('toggle');
            }
        },
    }
</script>

<style scoped>

</style>
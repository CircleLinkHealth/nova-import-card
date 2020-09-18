<template>
    <div>
        <div v-if="totalHours > 0">
            <label v-show="!edited" @click="edit()" class="edit-daily-work-hours text-center" style="padding: 2px 0;">{{workHours[day]}} hrs</label>

            <input v-show="edited" type="number" :min="min" :max="max" class="form-control edit-daily-work-hours"
                v-model="workHours[day]"
                @blur="doneEdit()"
                @keyup.enter="hideEdited()"
                @keyup.esc="cancelEdit()">
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'
    import {addNotification} from '../../../../../../../../resources/assets/js/store/actions'
    export default {
        props: ['day', 'hours', 'windows'],

        created() {
            if (this.hours) {
                this.workHours = JSON.parse(this.hours)
            }

            if (this.windows) {
                this.dayWindows = JSON.parse(this.windows)
            }

            if (this.totalHours === 0) {
                this.workHours[this.day] = null
                this.saveHours(true)
            }

            if (this.workHours[this.day] > this.totalHours) {
                this.workHours[this.day] = this.totalHours
                this.saveHours(true)
            }
        },

        data() {
            return {
                workHours: {},
                dayWindows: {},
                edited: false,
                beforeEditCache: null,
                min: 1,
                max: 12
            }
        },

        computed: {
            totalHours() {
                let total = 0;
                for (let i = 0; i < this.dayWindows.length; i++) {
                    total += this.hoursDifference(this.dayWindows[i].window_time_start, this.dayWindows[i].window_time_end)
                }
                return total
            }
        },

        methods: Object.assign(mapActions(['addNotification']), {
            edit() {
                this.beforeEditCache = this.workHours[this.day]
                this.edited = true
            },

            hideEdited() {
                this.edited = false
            },

            doneEdit() {
                this.edited = false

                if (this.workHours[this.day] > 12 || this.workHours[this.day] < 1) {
                    this.workHours[this.day] = this.beforeEditCache

                    this.addNotification({
                        title: "Invalid number of work hours.",
                        text: "Enter a number between 1 and 12.",
                        type: "danger",
                        timeout: true
                    })
                } else if (this.workHours[this.day] > this.totalHours) {
                    this.workHours[this.day] = this.beforeEditCache

                    this.addNotification({
                        title: "Invalid number of work hours.",
                        text: "Daily work hours cannot be more than total window hours.",
                        type: "danger",
                        timeout: true
                    })
                } else {
                    this.saveHours()
                }
            },

            cancelEdit() {
                this.workHours[this.day] = this.beforeEditCache
                this.edited = false
            },

            saveHours(hideNotification = false) {
                if (_.isUndefined(this.workHours.id)) {
                    return
                }

                this.axios.patch('work-hours/' + this.workHours.id, {
                    workHours: this.workHours[this.day],
                    day: this.day
                }).then((response) => {
                    if (!hideNotification) {
                        this.addNotification({
                            title: "Successfully updated hours.",
                            text: "",
                            type: "success",
                            timeout: true
                        })
                    }
                }).catch((error) => {
                    console.log(error);
                })
            },

            hoursDifference(startTime, endTime) {
                //hack, since we only have time and no date
                let start = new Date('2017/01/01 ' + startTime)
                let end = new Date('2017/01/01 ' + endTime)

                return Math.floor((end - start) / 1000 / 60 / 60);
            },
        })
    }
</script>

<style>
    .edit-daily-work-hours {
        height: 25px;
        color: blue;
    }

    .edit-daily-work-hours:hover {
        cursor: pointer;
    }
</style>
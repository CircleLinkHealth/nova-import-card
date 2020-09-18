<template>
    <div>
        <div class="inactivity-tracker">{{time}}</div>
        <inactivity-modal></inactivity-modal>
    </div>
</template>

<script>
    /**
     * Inactivity Tracker keeps track of the time the user is inactive on the page.
     */

    import EventBus from './event-bus'
    import { rootUrl } from '../../../app.config'
    import InactivityModal from './modals/inactivity-modal'

    export default {
        props: {
            callMode: Boolean
        },
        data() {
            return {
                startTime: new Date(),
                endTime: new Date(),
                isModalShown: false,
                ALERT_TIMEOUT: 120,
                LOGOUT_TIMEOUT: 600,
                ALERT_TIMEOUT_CALL_MODE: 1800,
                LOGOUT_TIMEOUT_CALL_MODE: 3600
            };
        },
        components: {
            'inactivity-modal': InactivityModal
        },
        methods: {
            pad(num, count) {
                num = Math.floor(num);
                count = count || Number.POSITIVE_INFINITY;
                const $num = num + "";
                return "0".repeat((count - $num.length) || 0) + $num;
            },
            start() {
                if (this.interval) clearInterval(this.interval);
                this.interval = setInterval(
                    function() {
                        this.endTime = new Date();
                        const ALERT_INTERVAL = () => !this.callMode ? this.ALERT_TIMEOUT : this.ALERT_TIMEOUT_CALL_MODE; // 120-900
                        const LOGOUT_INTERVAL = () => !this.callMode ? this.LOGOUT_TIMEOUT : this.LOGOUT_TIMEOUT_CALL_MODE; // 600-1200
                        if (this.totalSeconds && !this.isModalShown && ((this.totalSeconds >= ALERT_INTERVAL()) && (this.totalSeconds < LOGOUT_INTERVAL()))) {
                            /**
                             * Stop Tracking Time
                             * Show Modal asking the user why he/she has been inactive
                             * 
                             * Disable the window.onfocus handler
                             */
                            this.windowFocusHandler = window.onfocus
                            window.onfocus = null;
                            EventBus.$emit("tracker:show-inactive-modal")
                            EventBus.$emit('modal-inactivity:show')
                            this.isModalShown = true
                        }
                        else if (this.totalSeconds && (this.totalSeconds >= LOGOUT_INTERVAL())) {
                            /**
                             * Logout the user automatically
                             */
                            EventBus.$emit("tracker:logout")
                        }
                    }.bind(this),
                    1000
                );
            },
            stop() {
                clearInterval(this.interval);
            },
            reset(e) {
                if (!this.isModalShown) this.startTime = this.endTime;
                else console.warn('attempt to reset inactivity-tracker rebuffed', this.time)

                this.startTime = this.endTime = new Date()
            }
        },
        computed: {
            elapsed() {
                return this.endTime - this.startTime;
            },
            hours() {
                return this.pad(Math.floor(this.elapsed / 1000 / 3600), 2);
            },
            minutes() {
                return this.pad(
                    Math.floor((this.elapsed / 1000 - this.hours * 3600) / 60),
                    2
                );
            },
            seconds() {
                return this.pad(
                    this.elapsed / 1000 - this.minutes * 60 - this.hours * 3600,
                    2
                );
            },
            totalSeconds() {
                return Math.floor(this.elapsed / 1000);
            },
            time() {
                return `${this.hours} : ${this.minutes} : ${this.seconds}`;
            }
        },
        mounted() {
            this.start();
            EventBus.$on("inactivity:reset", this.reset.bind(this));
            EventBus.$on("inactivity:stop", this.stop.bind(this));
            EventBus.$on("inactivity:start", this.start.bind(this));

            EventBus.$on('modal-inactivity:close', (preventEmit) => {
                EventBus.$emit("tracker:hide-inactive-modal")
                this.isModalShown = false
                this.reset()
                console.log('modal closed')
                //restore the window.onfocus handler
                if (this.windowFocusHandler) window.onfocus = this.windowFocusHandler
            })

            EventBus.$on('modal-inactivity:reset', (preventEmit) => {
                this.reset()
                if (this.windowFocusHandler) window.onfocus = this.windowFocusHandler 
            })

            EventBus.$on('modal-inactivity:timeouts:override', (options = {}) => {
                try {
                    this.ALERT_TIMEOUT = options.alertTimeout || this.ALERT_TIMEOUT
                    this.LOGOUT_TIMEOUT = options.logoutTimeout || this.LOGOUT_TIMEOUT
                    this.ALERT_TIMEOUT_CALL_MODE = options.alertTimeoutCallMode || this.ALERT_TIMEOUT_CALL_MODE
                    this.LOGOUT_TIMEOUT_CALL_MODE = options.logoutTimeoutCallMode || this.LOGOUT_TIMEOUT_CALL_MODE

                    EventBus.$emit('tracker:timeouts:override', {
                        alertTimeout: this.ALERT_TIMEOUT,
                        logoutTimeout: this.LOGOUT_TIMEOUT,
                        alertTimeoutCallMode: this.ALERT_TIMEOUT_CALL_MODE,
                        logoutTimeoutCallMode: this.LOGOUT_TIMEOUT_CALL_MODE
                    })
                }
                catch (err) {
                    console.error(err)
                }
            })
        }
    }
</script>

<style>
    .inactivity-tracker {
        display: none;
    }
</style>
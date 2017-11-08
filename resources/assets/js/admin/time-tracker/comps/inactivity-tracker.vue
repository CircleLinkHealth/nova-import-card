<template>
    <div class="activity-tracker">{{time}}</div>
</template>

<script>
    import EventBus from './event-bus'

    export default {
        data() {
            return {
                startTime: new Date(),
                endTime: new Date()
            };
        },
        methods: {
            pad(num, count) {
                num = Math.floor(num);
                count = count || Number.POSITIVE_INFINITY;
                const $num = num + "";
                return "0".repeat(count - $num.length) + $num;
            },
            start() {
                if (this.interval) clearInterval(this.interval);
                this.interval = setInterval(
                    function() {
                    this.endTime = new Date();
                    const ALERT_INTERVAL = 59;
                    if (this.seconds >= ALERT_INTERVAL) {
                        if (!this.alertIsActive) {
                            EventBus.$emit("tracker:stop");
                            this.alertIsActive = true;
                            alert(
                                `${ALERT_INTERVAL} seconds have elapsed since your last activity`
                            );
                            this.alertIsActive = false;
                            EventBus.$emit("tracker:start");
                            
                            this.reset();
                        }
                    }
                    }.bind(this),
                    1000
                );
            },
            stop() {
                clearInterval(this.interval);
            },
            reset() {
                this.startTime = this.endTime;
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
            time() {
                return `${this.hours} : ${this.minutes} : ${this.seconds}`;
            }
        },
        mounted() {
            this.start();
            EventBus.$on("inactivity:reset", this.reset.bind(this));
            EventBus.$on("inactivity:stop", this.stop.bind(this));
            EventBus.$on("inactivity:start", this.start.bind(this));
            console.log("inactivity's parent", EventBus)
        }
    }
</script>

<style>
    
</style>
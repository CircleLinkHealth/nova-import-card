<template>
    <span class="time-display" :class="{inactive: !noLiveCount && !running}">
        <a :href="redirectUrl">{{time}}</a>
    </span>
</template>

<script>
    import EventBus from './event-bus'

    export default {
        name: 'time-display',
        props: ['seconds', 'no-live-count', 'redirectUrl'],
        data: () => {
            return {
                running: false
            };
        },
        computed: {
            hours() {
                return this.pad(Math.floor(this.seconds / 3600), 2)
            },
            minutes() {
                return this.pad((Math.floor(this.seconds / 60) % 60), 2)
            },
            time() {
                return `${this.hours}:${this.minutes}:${this.pad(this.seconds % 60, 2)}`;
            }
        },
        methods: {
            getTime() {
                return this.time;
            },
            pad(num, count) {
                count = count || 0;
                const $num = num + '';
                return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
            },
            start() {
                if (!this.noLiveCount) {
                    this.running = true;
                    const STEP = 1000;
                    if (this.interval) clearInterval(this.interval);
                    this.interval = setInterval((function () {
                        EventBus.$emit("tracker:tick")
                    }).bind(this), STEP)
                }
            },
            stop() {
                //never stop the timer, let it show but with a red font.
                this.running = false;
                // clearInterval(this.interval)
            }
        },
        mounted() {
            this.start()
            EventBus.$on('tracker:start', this.start.bind(this));
            EventBus.$on('tracker:stop', this.stop.bind(this));
        }
    }
</script>

<style>

    .inactive {
        color: red;
    }

</style>

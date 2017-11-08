<template>
    <div class="time-display">{{time}}</div>
</template>

<script>
    export default {
        name: 'time-display',
        props: ['seconds'],
        computed: {
            hours() {
                return this.pad(Math.floor(this.seconds / 3600), 2)
            },
            minutes() {
                return this.pad(Math.floor(this.seconds / 60), 2)
            },
            time() {
                return `${this.hours} : ${this.minutes} : ${this.pad(this.seconds % 60, 2)}`;
            }
        },
        methods: {
            pad (num, count) {
                count = count || 0;
                const $num = num + '';
                return '0'.repeat(count - $num.length) + $num;
            },
            start() {
                const STEP = 1000;
                if (this.interval) clearInterval(this.interval);
                this.interval = setInterval((function () {
                    this.$parent.$emit("tracker:tick")
                }).bind(this), STEP)
            },
            stop() {
                clearInterval(this.interval)
            }
        },
        mounted() {
            this.start()
            this.$parent.$on('tracker:start', this.start.bind(this));
            this.$parent.$on('tracker:stop', this.stop.bind(this));
        }
    }
</script>

<style>
    
</style>
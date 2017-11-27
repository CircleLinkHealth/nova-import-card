<template>
    <div v-if="visible">{{showDefault ? value : time}}</div>
</template>

<script>

    export default {
        name: 'server-time-display',
        props: ['url', 'patient-id', 'provider-id', 'value'],
        data() {
            return {
                seconds: 0,
                visible: false,
                showDefault: false
            }
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
            pad (num, count) {
                count = count || 0;
                const $num = num + '';
                return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
            }
        },
        mounted() {
            this.$http.get(`${this.url}/${this.providerId}/${this.patientId}`).then((res) => {
                this.visible = true
                this.seconds = ((res.data || {}).totalTime || 0)
                console.log('server-time-display', this.seconds, this.time, this.value)
            }).catch((err) => {
                this.showDefault = true
                this.visible = true
                console.error('server-time-display', err)
            })
        }
    }
</script>

<style>
    
</style>
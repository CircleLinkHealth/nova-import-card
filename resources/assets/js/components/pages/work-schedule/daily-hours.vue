<script>
    export default {
        props: ['day', 'hours'],

        mounted() {
            if (this.hours) {
                this.workHours = JSON.parse(this.hours)
            }
        },

        data() {
            return {
                workHours: {}
            }
        }
    }
</script>

<template>
    <div>
        {{workHours[day]}}
    </div>
</template>
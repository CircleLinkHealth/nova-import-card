<template>
    <div>
        <div class="no-live-count">
            <div v-for="csSummary in chargeableServices" :class="getClassName()">
                <div>
                    <small>{{ csSummary.chargeable_service.display_name }}</small>
                </div>
                <div>
                    <a :href="routeActivities" :id="'monthly-time-' + sanitizeCsName(csSummary.chargeable_service.display_name)">
                        {{ formatTime(csSummary.total_time) }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "time-display-all-chargeable-services-static",
        props: [
            'chargeableServices',
            'routeActivities'
        ],
        methods: {
            formatTime(seconds) {
                return new Date(seconds * 1000).toISOString().substr(11, 8);
            },
            getClassName() {
                const len = this.chargeableServices.length;
                switch (len) {
                    case 1:
                        return 'col-md-12';
                    case 2:
                        return 'col-md-6';
                    case 3:
                        return 'col-md-4';
                    default:
                        return 'col';
                }
            },
            sanitizeCsName(csName){
                return csName.replace(/[^A-Z0-9]/ig, "_");
            }
        }
    }
</script>

<style scoped>
    .no-live-count {
        margin: auto;
    }

    .no-live-count div {
        padding-right: 0;
        padding-left: 0;
    }
</style>

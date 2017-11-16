<script>
    export default {
        props: {
            practiceProp: {
                type: [Number, String],
                required: false,
                default: null
            },
            locationProp: {
                type: [Number, String],
                required: false,
                default: null
            },
            billingProviderProp: {
                type: [Number, String],
                required: false,
                default: null
            },
        },

        data() {
            this.axios.get('api/practices/all')
                .then((response) => {
                    this.practices = response.data
                })

            return {
                practice: JSON.parse(JSON.stringify(this.practiceProp)),
                location: JSON.parse(JSON.stringify(this.locationProp)),
                billingProvider: JSON.parse(JSON.stringify(this.billingProviderProp)),
                practices: [],
                locationsCollection: [],
                providersCollection: [],
            }
        },

        computed: {
            locations: function () {
                if (!this.practice) {
                    this.location = null;
                    this.billingProvider = null;
                    this.providersCollection = [];

                    return [];
                }

                this.locationsCollection = this.practices[this.practice].locations;

                return this.locationsCollection;
            },

            providers: function () {
                if (!this.location || !this.practices[this.practice].locations[this.location]) {
                    this.billingProvider = null;
                    this.providersCollection = [];

                    return [];
                }

                this.providersCollection = this.locations[this.location].providers;

                return this.providersCollection;
            }
        }
    }
</script>

<template v-cloak>
    <div class="row panel">
        <div class="col-md-4">
            <h3>Select <b>Practice</b></h3>

            <select2 v-model="practice" class="col-md-12" name="practiceId">
                <option v-for="p in practices" :key="p.id" :value="p.id">{{ p.display_name }}</option>
            </select2>
        </div>
        <div class="col-md-4 left-border">
            <h3>Select <b>Location</b></h3>

            <select v-model="location" class="col-md-12" name="locationId">
                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
            </select>
        </div>
        <div class="col-md-4 left-border">
            <h3>Select <b>Billing Provider</b></h3>

            <select v-model="billingProvider" class="col-md-12" name="billingProviderId">
                <option v-for="prov in providers" :key="prov.id"
                        :value="prov.id">{{ prov.first_name }} {{ prov.last_name }}</option>
            </select>
        </div>
    </div>
</template>

<style scoped>
    .panel {
        border: 2px solid #eeeeee;
        padding: 2rem 3rem 6rem 3rem;
        margin: 2rem;
        border-radius: 2rem;
    }

    .left-border {
        border-left: 1px solid #ededed;
    }
</style>
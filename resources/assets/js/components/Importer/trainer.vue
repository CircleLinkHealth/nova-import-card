<script>
    export default {
        props: {
            practice: {
                type: [Number, String],
                required: false,
                default: null
            },
            location: {
                type: [Number, String],
                required: false,
                default: null
            },
            billingProvider: {
                type: [Number, String],
                required: false,
                default: null
            },
        },

        data() {
            window.axios.get('/practices/all')
                .then((response) => {
                    this.practices = response.data
                })

            return {
                practices: [],
                locationsCollection: [],
                providersCollection: [],
            }
        },

        computed: {
            locations: function () {
                if (_.isNull(this.practice)) {
                    this.location = null;
                    this.billingProvider = null;
                    this.providersCollection = [];

                    return [];
                }

                this.locationsCollection = this.practices[this.practice].locations;

                return this.locationsCollection;
            },

            providers: function () {
                if (this.location === null || !this.practices[this.practice].locations[this.location]) {
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

<template>
    <div class="row">
        <div class="col-md-4">
            <h1>Practice</h1>

            <select2 v-model="practice" class="col-md-12" name="practiceId">
                <option v-for="p in practices" :value="p.id">{{ p.display_name }}</option>
            </select2>
        </div>
        <div class="col-md-4">
            <h1>Location</h1>

            <select v-model="location" class="col-md-12" name="locationId">
                <option v-for="l in locations" :value="l.id">{{ l.name }}</option>
            </select>
        </div>
        <div class="col-md-4">
            <h1>Billing Provider</h1>

            <select v-model="billingProvider" class="col-md-12" name="billingProviderId">
                <option v-for="prov in providers"
                        :value="prov.id">{{ prov.first_name }} {{ prov.last_name }}</option>
            </select>
        </div>
    </div>
</template>
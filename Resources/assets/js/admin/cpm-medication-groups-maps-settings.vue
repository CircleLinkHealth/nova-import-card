<template>
    <div class="container">

        <div class="row">
            <notifications ref="notifications" name="admin-medication-group-maps"></notifications>
        </div>

        <div class="row">
            <h3>Add new Medication Group Map</h3>

            <p>For the Add button to become Enabled, fill in the keyword, and select a Medication Group.</p>
            <p>The Importer will apply pattern matching to see if medications contain the keywords, and activate the
                relevant groups. For example 'asp' will match 'aspirin'.</p>
            <br>
            <div class="form-group">
                <div class="col-md-4">
                    <input class="form-control" v-model="newMap.keyword" type="text"
                           placeholder="Medication Name or Keyword" required>
                </div>

                <div class="col-md-5">
                    <select2 :options="groups" v-model="newMap.medication_group_id"
                             style="width: 100%;height: 100%;">
                        <option selected value="0">Select a medication group</option>
                    </select2>
                </div>

                <div class="col-md-3">
                    <div class="btn btn-primary col-md-12" @click="store" :disabled="!isValid">
                        Store
                        <loader v-if="loaders.isSaving"></loader>
                    </div>
                </div>
            </div>

            <br><br>

            <div class="row">
                <h3>Existing Maps</h3>

                <p v-show="maps.length == 0">Nothing to see here. Get back to adding maps!</p>

                <ul style="list-style: none;">
                    <li v-for="map in maps">
                        <div class="col-md-4">{{ map.keyword }}</div>
                        <div class="col-md-4">{{ map.medication_group }}</div>
                        <div class="col-md-4">
                            <div class="btn btn-xs btn-danger problem-delete-btn" @click.stop.prevent="remove(map.id)">
                                <span>
                                    <i class="glyphicon glyphicon-remove"></i>
                                </span>
                                <loader v-if="loaders.isDeleting.find((l) => {return l === map.id})"></loader>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import {addNotification} from '../../../../../Sharedvuecomponents/Resources/assets/js/store/actions'
    import {mapActions} from 'vuex'

    export default {
        computed: {
            isValid() {
                return this.newMap.keyword && this.newMap.medication_group_id;
            },
            maps() {
                return (this.mapsCollection || []).sort((a, b) => {
                    return a.keyword.localeCompare(b.keyword);
                });
            },
            groups() {
                return (this.groupsCollection || []).sort((a, b) => {
                    return a.text.localeCompare(b.text);
                });
            },
        },

        methods: Object.assign(
            mapActions(['addNotification']),
            {
                remove(id) {
                    let self = this

                    if (confirm("Are you sure you want to delete this medication map?")) {
                        self.loaders.isDeleting.push(id)

                        self.axios.delete(self.destroyMapUrl + '/' + id, {})
                            .then((response) => {
                                self.mapsCollection = self.mapsCollection.filter(m => m.id !== id);

                                self.loaders.isDeleting = self.loaders.isDeleting.filter(l => l !== id);

                                self.addNotification({
                                    title: "Successfully deleted mapping",
                                    text: "",
                                    type: "info",
                                    timeout: true
                                });
                            })
                            .catch((error) => {
                                self.addNotification({
                                    title: "Could not delete mapping.",
                                    text: "Please try again",
                                    type: "danger",
                                    timeout: false
                                });
                            });
                    }
                },

                store() {
                    let self = this
                    self.loaders.isSaving = true
                    self.axios.post(self.storeMapUrl, {
                        'keyword': self.newMap.keyword,
                        'medication_group_id': self.newMap.medication_group_id
                    }).then((response) => {
                        self.mapsCollection.push({
                            id: response.data.stored.id,
                            keyword: response.data.stored.keyword,
                            medication_group_id: response.data.stored.medication_group_id,
                            medication_group: response.data.stored.cpm_medication_group.name,
                        });

                        self.loaders.isSaving = false

                        self.addNotification({
                            title: "Successfully stored mapping",
                            text: response.data.stored.keyword + ' will now activate tag ' + response.data.stored.cpm_medication_group.name,
                            type: "success",
                            timeout: true
                        });

                        self.newMap.keyword = null
                        self.newMap.medication_group_id = null
                    }).catch((error) => {
                        self.addNotification({
                            title: "Could not store mapping.",
                            text: "Please try again",
                            type: "danger",
                            timeout: false
                        });
                    });
                },
                document() {
                    return (typeof (document) === 'undefined') ? {
                        querySelector: (query) => ({getAttribute: () => null})
                    } : document
                }
            }),

        props: {
            medicationGroupsMaps: String,
            medicationGroups: String,
        },

        data() {
            return {
                storeMapUrl: this.document().querySelector('meta[name="medication.groups.maps.store"]').getAttribute('content'),
                destroyMapUrl: this.document().querySelector('meta[name="medication.groups.maps.destroy"]').getAttribute('content'),
                newMap: {
                    keyword: '',
                    medication_group_id: '',
                },
                mapsCollection: JSON.parse(this.medicationGroupsMaps),
                groupsCollection: JSON.parse(this.medicationGroups),
                loaders: {
                    isSaving: false,
                    isDeleting: [],
                }
            }
        }
        ,
    }
</script>

<style>

</style>
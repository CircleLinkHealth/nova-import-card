<template>
    <div class="admin-panel-locations-container" v-cloak>
        <div class="row">
            <div class="col s6">
                <div class="input-field">
                    <div v-on:click=""
                         class="btn blue waves-effect waves-light" id="submit">
                        Add New Location
                    </div>
                </div>
            </div>

            <div class="col s6">
                <div class="input-field">
                    <input id="search" type="search" name="query" v-model="searchQuery"
                           placeholder="search for a location">
                    <label class="label-icon" for="search"><i class="material-icons">search</i></label>
                    <i class="material-icons" @click="searchQuery = ''">close</i></div>
            </div>
        </div>

        <grid
                :data="formattedLocations"
                :options="gridOptions"
                :filter-key="searchQuery"
                @click="cellClicked">
        </grid>

        <component :is="compName" :show="showModal" :location="editedLocation" @close-modal="closeModal()"></component>

    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {practiceLocations} from '../../../store/getters'
    import {getPracticeLocations} from '../../../store/actions'
    import UpdateLocation from './update.vue'

    export default {
        components: {
            UpdateLocation
        },

        computed: Object.assign({},
            mapGetters({
                locations: 'practiceLocations'
            }),
            {
                formattedLocations() {
                    return JSON.parse(JSON.stringify(this.gridData))
                }
            }
        ),

        created() {
            this.getPracticeLocations(this.practiceId)
            this.gridData = this.locations
        },

        methods: Object.assign({},
            mapActions(['getPracticeLocations']),
            {
                cellClicked(index, entry, entryIndex) {
                    switch (index) {
                        case 'trash':
                            this.deleteRow(entryIndex)
                            break;
                        case 'edit':
                            this.editRow(entryIndex)
                            break;
                        default:
                            break;
                    }
                },

                deleteRow(index) {
                    let disassociate = confirm('Are you sure you want to delete ' + this.gridData[index].name + '?');

                    if (!disassociate) {
                        return true;
                    }

                    this.gridData.splice(index, 1)
                },

                editRow(index) {
                    this.compName = 'update-location'
                    this.editedLocation = this.gridData[index]
                    this.showModal = true
                },

                closeModal() {
                    this.compName = ''
                    this.editedLocation = {}
                    this.showModal = false
                }
            }),

        data() {
            return {
                compName: '',
                showModal: false,
                editedLocation: {},
                searchQuery: '',
                gridOptions: {
                    columns: {
                        edit: {
                            name: '',
                            content: '<a class="green waves-effect waves-light btn" style="padding: 0 .4rem;"><i class="material-icons center">mode_edit</i></a>'
                        },
                        trash: {
                            name: '',
                            content: '<a class="red waves-effect waves-light btn" style="padding: 0 .4rem;"><i class="material-icons center text-white">clear</i></a>',
                        },
                        name: {
                            name: 'Name'
                        },
                        address_line_1: {
                            name: 'Address Line 1'
                        },
                        city: {
                            name: 'City'
                        },
                        state: {
                            name: 'State'
                        },
                    }
                },
                practiceId: $('meta[name=practice-id]').attr('content'),
                gridData: []
            }
        },
    }
</script>

<style>
    .admin-panel-locations-container .input-field {
        margin-top: 0;
    }

    th.th-trash, td.td-trash, th.th-edit, td.td-edit {
        /*background: none;*/
        width: 10px;
        min-width: 5px;
        padding: 10px 0;
    }
</style>
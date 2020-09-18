<template>
    <div class="admin-panel-locations-container" v-cloak>
        <div class="row">
            <div class="col s6">
                <div class="input-field">
                    <div @click="addLocation()"
                         class="btn blue waves-effect waves-light" id="submit">
                        Add New Location
                    </div>
                </div>
            </div>

            <div class="col s6">
                <div class="input-field">
                    <i class="material-icons" style="position: absolute;top: 0.7rem;">search</i>
                    <input id="search" type="search" name="query" v-model="searchQuery"
                           placeholder="search for a location">
                    <i class="material-icons" @click="searchQuery = ''">close</i></div>
            </div>
        </div>

        <grid
                :data="locations"
                :options="gridOptions"
                :filter-key="searchQuery"
                @click="cellClicked">
        </grid>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {practiceLocations} from '../../../store/getters'
    import {getPracticeLocations, deletePracticeLocation} from '../../../store/actions'

    export default {
        computed: Object.assign({},
            mapGetters({
                locations: 'practiceLocations'
            })
        ),

        mounted() {
            this.getPracticeLocations(this.practiceId)
        },

        methods: Object.assign({},
            mapActions(['getPracticeLocations', 'deletePracticeLocation']),
            {
                cellClicked(action, entry) {
                    switch (action) {
                        case 'trash':
                            this.deleteRow(entry)
                            break;
                        case 'edit':
                            this.editRow(entry)
                            break;
                        default:
                            break;
                    }
                },

                deleteRow(entry) {
                    let disassociate = confirm('Are you sure you want to delete ' + _.find(this.locations, ['id', entry.id]).name + '?');

                    if (!disassociate) {
                        return true;
                    }

                    this.deletePracticeLocation(_.find(this.locations, ['id', entry.id]))
                },

                editRow(entry) {
                    this.$emit('update-view', 'update-location', _.find(this.locations, ['id', entry.id]))
                },

                addLocation() {
                    this.$emit('update-view', 'update-location')
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
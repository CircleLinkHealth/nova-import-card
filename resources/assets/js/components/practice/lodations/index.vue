<template>
    <div class="admin-panel-locations-container">
        <div class="row">
            <div class="col s8">
                <div class="input-field">
                    <div v-on:click=""
                         class="btn blue waves-effect waves-light" id="submit">
                        Add New Location
                    </div>
                </div>
            </div>

            <div class="col s4">
                <div class="input-field">
                    <input id="search" type="search" name="query" v-model="searchQuery"
                           placeholder="search for a location">
                    <label class="label-icon" for="search"><i class="material-icons">search</i></label>
                    <i class="material-icons">close</i></div>
            </div>
        </div>

        <grid
                :data="formattedLocations"
                :columns="gridColumns"
                :filter-key="searchQuery">
        </grid>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {practiceLocations} from '../../../store/getters'
    import {getPracticeLocations} from '../../../store/actions'

    export default {
        computed: Object.assign({},
            mapGetters({
                locations: 'practiceLocations'
            }),
            {
                formattedLocations() {
                    return JSON.parse(JSON.stringify(this.gridData)).map((loc) => {
                        loc.name = '<i class="material-icons">mode_edit</i>'+loc.name

                        return loc
                    })
                }
            }
        ),

        created() {
            this.getPracticeLocations(this.practiceId)
            this.gridData = this.locations
        },

        data() {
            return {
                searchQuery: '',
                gridColumns: ['name', 'address_line_1', 'city', 'state'],
                practiceId: $('meta[name=practice-id]').attr('content'),
                gridData: []
            }
        },

        methods: Object.assign({},
            mapActions(['getPracticeLocations'])
        ),
    }
</script>

<style>
    .admin-panel-locations-container .input-field {
        margin-top: 0;
    }
</style>
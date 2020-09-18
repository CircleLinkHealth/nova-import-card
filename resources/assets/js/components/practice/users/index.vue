<template>
    <div class="admin-panel-staff-container" v-cloak>
        <div class="row">
            <div class="col s6">
                <div class="input-field">
                    <div @click="addUser()"
                         class="btn blue waves-effect waves-light" id="submit">
                        Add New Staff Member
                    </div>
                </div>
            </div>

            <div class="col s6">
                <div class="input-field">
                    <i class="material-icons" style="position: absolute;top: 0.7rem;">search</i>
                    <input id="search" type="search" name="query" v-model="searchQuery"
                           placeholder="search for a staff member">
                    <i class="material-icons" @click="searchQuery = ''">close</i></div>
            </div>
        </div>

        <grid
                :data="users"
                :options="gridOptions"
                :filter-key="searchQuery"
                @click="cellClicked">
        </grid>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {practiceStaff} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/getters'
    import {getPracticeStaff, deletePracticeStaff, getPracticeLocations} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/actions'

    export default {
        computed: Object.assign({},
            mapGetters({
                users: 'practiceStaff'
            })
        ),

        mounted() {
            this.getPracticeStaff(this.practiceId)
            this.getPracticeLocations(this.practiceId)
        },

        methods: Object.assign({},
            mapActions(['getPracticeStaff', 'deletePracticeStaff', 'getPracticeLocations']),
            {
                cellClicked(index, entry) {
                    switch (index) {
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
                    let disassociate = confirm('Are you sure you want to delete ' + _.find(this.users, ['id', entry.id]).full_name + '?');

                    if (!disassociate) {
                        return true;
                    }

                    this.deletePracticeStaff(_.find(this.users, ['id', entry.id]))
                },

                editRow(entry) {
                    this.$emit('update-view', 'update-staff', _.find(this.users, ['id', entry.id]))
                },

                addUser() {
                    this.$emit('update-view', 'update-staff')
                }
            }),

        data() {
            return {
                compName: '',
                showModal: false,
                editedUser: {},
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
                        full_name: {
                            name: 'Name'
                        },
                        role_display_names: {
                            name: 'Role'
                        },
                        phone_number: {
                            name: 'Phone'
                        },
                    }
                },
                practiceId: $('meta[name=practice-id]').attr('content'),
            }
        },
    }
</script>

<style>
    .admin-panel-staff-container .input-field {
        margin-top: 0;
    }

    th.th-trash, td.td-trash, th.th-edit, td.td-edit {
        /*background: none;*/
        width: 10px;
        min-width: 5px;
        padding: 10px 0;
    }
</style>
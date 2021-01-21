<template>
    <modal name="allergies" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12 pad-top-10" :class="{ 'allergy-container': isExtendedView }">
                    <div class="btn-group" role="group" :class="{ 'allergy-buttons': isExtendedView }">
                        <div class="btn btn-secondary allergy-button" :class="{ selected: selectedAllergy && (selectedAllergy.id === allergy.id) }" 
                                v-for="(allergy, index) in allergiesForListing" :key="index" @click="select(index)">
                            {{allergy.name}}
                            <span class="delete" title="remove this cpm allergy" @click="removeAllergy">x</span>
                            <loader class="absolute" v-if="loaders.removeAllergy && selectedAllergy && (selectedAllergy.id === allergy.id)"></loader>
                        </div>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedAllergy || !selectedAllergy.id }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12" v-if="!selectedAllergy">
                    <form @submit="addAllergy">
                        <div class="form-group">
                            <div class="top-20">
                                <input type="text" class="form-control color-black" placeholder="Enter a title" :class="{ error: alreadyExists }" v-model="newAllergy.name" required />
                            </div>
                            <div class="top-20 text-right">
                                <loader v-if="loaders.addAllergy"></loader>
                                <button class="btn btn-secondary selected" :disabled="alreadyExists">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/common/modal'

    export default {
        name: 'care-areas-modal',
        props: {
            'patient-id': String,
            allergies: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
            isExtendedView() {
                return this.allergies.length > 12
            },
            alreadyExists() {
                return !!this.allergies.find(allergy => allergy.name == this.newAllergy.name)
            },
            allergiesForListing() {
                return this.allergies.distinct(allergy => allergy.name)
            }
        },
        data() {
            return {
                newAllergy: {
                    name: null
                },
                selectedAllergy: null,
                loaders: {
                    addAllergy: null,
                    removeAllergy: null
                }
            }
        },
        methods: {
            select(index) {
                this.selectedAllergy = (index >= 0) ? Object.assign({}, this.allergies[index]) : null
            },
            reset() {
                this.newAllergy.name = ''
            },
            addAllergy(e) {
                e.preventDefault()
                this.loaders.addAllergy = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/allergies`), { 
                            name: this.newAllergy.name
                    }).then(response => {
                        console.log('allergies:add', response.data)
                        this.loaders.addAllergy = false
                        Event.$emit('allergies:add', response.data)
                        this.reset()
                    }).catch(err => {
                        console.error('allergies:add', err)
                        this.loaders.addAllergy = false
                    })
            },
            removeAllergy(e) {
                if (this.selectedAllergy && confirm('Are you sure you want to remove this allergy?')) {
                    const allergyId = this.selectedAllergy.id
                    this.loaders.removeAllergy = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/allergies/${this.selectedAllergy.id}`)).then(response => {
                        console.log('allergies:remove-allergy', response.data)
                        this.loaders.removeAllergy = false
                        this.selectedAllergy = null
                        Event.$emit('allergies:remove', allergyId)
                    }).catch(err => {
                        console.error('care-areas:remove-allergy', err)
                        this.loaders.removeAllergy = false
                    })
                }
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .allergy-container {
        overflow-x: scroll;
    }

    .allergy-buttons {
        width: 2000px;
    }

    .allergy-button span.delete {
        width: 20px;
        height: 20px;
        font-size: 12px;
        background-color: #FA0;
        color: white;
        padding: 1px 5px;
        border-radius: 50%;
        position: absolute;
        top: -8px;
        right: -10px;
        cursor: pointer;
        display: none;
    }

    .allergy-button.selected span.delete {
        display: inline-block;
    }

    button.allergy-button div.loader.absolute {
        right: -13px;
        top: 15px;
    }

    .pad-top-10 {
        padding-top: 10px;
    }

    input.color-black {
        color: black;
    }
</style>
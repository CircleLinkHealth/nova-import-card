<template>
    <div>
        <div v-if="showSuccessBanner" class="alert alert-success">
            Successfully stored task!
        </div>
        <add-action-modal ref="addActionModal"></add-action-modal>
    </div>
</template>
<script>
    import {Event} from 'vue-tables-2'
    import AddActionModal from './modals/add-action.modal'
    import UserRolesHelperMixin from '../../../mixins/user-roles-helpers.mixin'

    export default {
        name: 'schedule-patient-activity',

        components: {
            'add-action-modal': AddActionModal,
        },

        mixins: [
            UserRolesHelperMixin
        ],

        props: [
            'patientId',
            'patientName',
            'practiceId',
            'practiceName',
            'type',
            'subType',
            'careCoachId',
            'careCoachName',
        ],

        data() {
            return {
                showSuccessBanner: false,
            };
        },

        mounted() {
            if (this.patientId && this.practiceId) {
                Event.$emit("modal-add-action:show")

                Event.$emit("add-action-modals:set", {
                    patientId: this.patientId,
                    patientName: this.patientName,
                    practiceId: this.practiceId,
                    practiceName: this.practiceName,
                    type: this.type,
                    subType: this.subType,
                    careCoachId: this.careCoachId,
                    careCoachName: this.careCoachName,
                })
            }

            Event.$on("modal-add-action:hide", (actions) => {
                if (actions && actions.length > 0) {
                    this.showSuccessBanner = true;
                }
            });
        }
    }
</script>
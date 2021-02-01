<template>
    <div v-if="!loaders.family && familyMembers.length > 0">
        <b>Family</b>:
        <span v-for="(member, index) in familyMembers">
            <a :href="getPatientUrl(member.user_id)">{{member.display_name}}</a>
            <span v-if="familyMembers.length > 0 && (familyMembers.length - 1) !== index">&nbsp;|&nbsp;&nbsp;</span>
        </span>
    </div>
</template>
<script>

    import {rootUrl} from "../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config";

    export default {
        name: 'patient-spouse',
        props: [
            'patientId',
        ],
        data() {
            return {
                familyMembers: [],
                loaders: {
                    family: false
                },
            }
        },
        methods: {
            getPatientUrl(patientId) {
                return rootUrl(`manage-patients/${patientId}/notes`);
            },
            getFamilyMembers() {
                this.loaders.family = true;
                const url = rootUrl(`manage-patients/${this.patientId}/family-members`);
                this.axios.get(url)
                    .then(resp => {
                        this.loaders.family = false;
                        if (resp.data) {
                            this.setData(resp.data);
                        }
                    })
                    .catch(err => {
                        this.loaders.family = false;
                        console.error(err);
                    });
            },

            setData(data) {
                if (!data || !data.members || !Array.isArray(data.members)) {
                    return;
                }
                // data.members => [{ user_id, display_name }]
                this.familyMembers = data.members;
            },
        },
        mounted() {
            if (!this.patientId) {
                console.error("PatientSpouse component missing patient id.")
                return;
            }
            this.getFamilyMembers();
        }
    }
</script>
<style>

    .pad-6 {
        padding: 6px;
        margin-left: -6px;
    }

    .loader-right {
        margin-top: -4px;
        float: right;
    }

</style>

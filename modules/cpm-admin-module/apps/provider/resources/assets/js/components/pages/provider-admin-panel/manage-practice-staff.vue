<template>
    <component :is="componentName" :editedStaffMember="props" :practice-settings="practiceSettings" @update-view="updateView"></component>
</template>

<script>
    import UpdateStaff from '../../practice/users/update.vue'
    import IndexStaff from '../../practice/users/index.vue'

    export default {

        props: [
            'practiceSettings'
        ],

        components: {
            UpdateStaff,
            IndexStaff
        },

        data() {
            return {
                componentName: 'index-staff',
                props: {},
            }
        },

        methods: {
            updateView(componentName, props) {
                this.componentName = componentName
                this.props = props
            }
        }
    }
</script>
<template>
    <component :is="componentName" :location="props" @update-view="updateView"></component>
</template>

<script>
    import UpdateStaff from '../../practice/users/update.vue'
    import IndexStaff from '../../practice/users/index.vue'

    export default {
        components: {
            UpdateStaff,
            IndexStaff
        },

        data() {
            return {
                componentName: 'index-staff',
                props: {}
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
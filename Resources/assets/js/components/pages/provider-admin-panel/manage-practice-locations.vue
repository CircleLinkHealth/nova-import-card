<template>
    <component :is="componentName" :location="props" @update-view="updateView"></component>
</template>

<script>
    import UpdateLocation from '../../../../../../../../resources/assets/js/components/practice/lodations/update.vue'
    import IndexLocations from '../../../../../../../../resources/assets/js/components/practice/lodations/index.vue'

    export default {
        components: {
            UpdateLocation,
            IndexLocations
        },

        data() {
            return {
                componentName: 'index-locations',
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
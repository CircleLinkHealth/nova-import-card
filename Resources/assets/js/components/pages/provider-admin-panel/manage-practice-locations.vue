<template>
    <component :is="componentName" :location="props" @update-view="updateView"></component>
</template>

<script>
    import UpdateLocation from '../../practice/lodations/update.vue'
    import IndexLocations from '../../practice/lodations/index.vue'

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
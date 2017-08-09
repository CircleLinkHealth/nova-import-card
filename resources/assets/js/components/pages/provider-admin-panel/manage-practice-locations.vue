<template>
    <!--<component-proxy v-if="componentName" :name="componentName" :props="props" @update-view="updateView"></component-proxy>-->
    <component :is="componentName" :location="props" @update-view="updateView"></component>
</template>

<script>
    import ComponentProxy from '../../shared/component-proxy.vue'
    import UpdateLocation from '../../practice/lodations/update.vue'
    import CreateLocations from '../../practice/lodations/create.vue'
    import IndexLocations from '../../practice/lodations/index.vue'

    export default {
        components: {
            UpdateLocation,
            IndexLocations,
            CreateLocations,
            ComponentProxy
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
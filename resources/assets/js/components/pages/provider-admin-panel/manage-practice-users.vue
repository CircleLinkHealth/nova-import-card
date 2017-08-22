<template>
    <component :is="componentName" :location="props" @update-view="updateView"></component>
</template>

<script>
    import UpdateUser from '../../practice/users/update.vue'
    import IndexUsers from '../../practice/users/index.vue'

    export default {
        components: {
            UpdateUser,
            IndexUsers
        },

        data() {
            return {
                componentName: 'index-users',
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
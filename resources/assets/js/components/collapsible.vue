<template>
    <div class="row">
        <div class="col-sm-12 alert alert-primary" :class="{ selected: stateData }" @click="toggle" title="Click to toggle">
            <slot name="title"></slot>
        </div>
        <div class="col-sm-12" v-if="stateData">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'collapsible',
        props: [ 'state' ],
        data() {
            return {
                stateData: this.state || false
            }
        },
        methods: {
            toggle (e) {
                this.stateData = !this.stateData
            }
        }
    }
</script>

<style>
    .alert.alert-primary {
        background-color: #ddd;
        border-color: transparent;
        color: black;
        cursor: pointer;
    }

    .alert.alert-primary.selected {
        background-color: #47beab;
        color: white;
    }

    .alert h4 {
        margin-bottom: 0px;
    }
</style>
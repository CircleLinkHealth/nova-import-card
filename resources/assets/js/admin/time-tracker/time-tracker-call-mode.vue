<template>
    <span class="call-mode" v-if="!noDisplay">
        <input class="btn btn-primary" type="button" value="Start Call Mode" @click="enterCallMode" v-if="Number(patientId) && (callMode === false)" />
        <input class="btn btn-danger" type="button" value="End Call Mode" @click="exitCallMode" v-if="Number(patientId) && (callMode === true)" />
        <loader v-if="(callMode === null) || loaders.callMode"></loader>
    </span>
</template>

<script>
    import EventBus from './comps/event-bus'
    import LoaderComponent from '../../components/loader'

    export default {
        props: {
            noDisplay: Boolean,
            patientId: Number
        },
        computed: {
            
        },
        data () {
            return {
                callMode: null,
                loaders: {
                    callMode: false
                }
            }
        },
        components: {
            'loader': LoaderComponent
        },
        methods: {
            enterCallMode () {
                this.loaders.callMode = true
                EventBus.$emit('tracker:call-mode:enter')
            },
            exitCallMode () {
                this.loaders.callMode = true
                EventBus.$emit('tracker:call-mode:exit')
            }
        },
        mounted () {
            EventBus.$on('server:call-mode', (callMode) => {
                this.callMode = callMode
                this.loaders.callMode = false
            })
        }
    }
</script>

<style>
    span.call-mode input[type='button'] {
        margin-top: 8px;
    }
</style>
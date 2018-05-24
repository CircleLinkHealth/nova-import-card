<template>
    <span class="call-mode" v-if="!noDisplay">
        <button class="btn btn-primary" type="button" 
            @click="enterCallMode" v-if="Number(patientId) && (callMode === false)">
            <span>Start Call Mode</span>
        </button>
        <button class="btn btn-danger" type="button" 
            @click="exitCallMode" v-if="Number(patientId) && (callMode === true)">
            <span>End Call Mode</span>
        </button>
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
            enterCallMode (e) {
                if (e) {
                    e.preventDefault()
                }
                this.loaders.callMode = true
                EventBus.$emit('tracker:call-mode:enter')
            },
            exitCallMode (e) {
                if (e) {
                    e.preventDefault()
                }
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
    span.call-mode button {
        margin-top: 10px;
    }
</style>
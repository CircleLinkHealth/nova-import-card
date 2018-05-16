<template>
    <span>
        <ul class="nav nav-pills bhi-nav" v-if="(isBhi && isCcm)">
            <li class="nav-item" :class="{ active: !isManualBehavioral }">
                <a class="nav-link" data-toggle="tab" role="tab" title="switch to CCM mode" @click="setBhi(false)">CCM</a>
            </li>
            <li class="nav-item" :class="{ active: isManualBehavioral }">
                <a class="nav-link" data-toggle="tab" role="tab" title="switch to BHI mode" @click="setBhi(true)">BHI</a>
            </li>
        </ul>
    </span>
</template>

<script>
    import EventBus from './event-bus'
    import { rootUrl } from '../../../app.config'
    import Modal from '../../common/modal'

    export default {
        props: {
            isBhi: {
                type: Boolean,
                default: false
            },
            isCcm: Boolean
        },
        computed: {
            
        },
        data () {
            return {
                isManualBehavioral: false
            }
        },
        components: {
            
        },
        methods: {
            setBhi (mode) {
                EventBus.$emit('tracker:bhi:switch', mode)
                this.isManualBehavioral = mode
            }
        },
        mounted () {
            console.log('isBhi', this.isBhi)
            console.log('isCcm', this.isCcm)


        }
    }
</script>

<style>
    ul.bhi-nav {
        display: inline-block;
        color: #50b2e2;
    }

    ul.bhi-nav li {
        cursor: pointer;
    }
    
</style>
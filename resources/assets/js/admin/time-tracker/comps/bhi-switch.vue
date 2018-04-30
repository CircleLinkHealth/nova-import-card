<template>
    <span>
        <ul class="nav nav-pills bhi-nav" v-if="isBhi || isCcm">
            <li class="nav-item" :class="{ active: isCcm }">
                <a class="nav-link" data-toggle="tab" role="tab" title="switch to CCM mode">CCM</a>
            </li>
            <li class="nav-item" :class="{ active: isBhi }">
                <a class="nav-link" data-toggle="tab" role="tab" title="switch to BHI mode">BHI</a>
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
            isBhi: Boolean,
            isCcm: Boolean
        },
        computed: {
            
        },
        data () {
            return {
                
            }
        },
        components: {
            
        },
        methods: {
            
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
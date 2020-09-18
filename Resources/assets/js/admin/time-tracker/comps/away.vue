<template>
    <div>
        <modal name="away" class-name="i-modal" :no-footer="true" 
            cancel-text="No" ok-text="Yes" :info="awayModalInfo" :no-wrapper-close="true">
            <template slot="title">
                You have gone idle ...
            </template>
            <template slot-scope="props">
                <div class="row">
                    <div class="col-sm-12" v-if="showGenericModal">
                        We haven’t heard from you in a while 😢. Are you still working?
                    </div>
                    <div class="col-sm-12" v-else>
                        We haven’t heard from you in a while 😢. Were you working on a specific patient while you were idle?
                    </div>
                </div>
            </template>
        </modal>
    </div>
</template>

<script>
    import EventBus from './event-bus'
    import { Event } from 'vue-tables-2'
    import { rootUrl } from '../../../app.config'
    import Modal from '../../../../../../../SharedVueComponents/Resources/assets/js/admin/common/modal'

    export default {
        name: 'away',
        components: {
            'modal': Modal
        },
        data() {
            return {
                showGenericModal: false,
                startTime: null,
                endTime: null,
                awayModalInfo: {
                    cancelHandler: (e) => {
                        EventBus.$emit('tracker:modal:reply', false)
                        EventBus.$emit('modal-away:hide')
                        EventBus.$emit("tracker:start")
                        EventBus.$emit("inactivity:start")
                    },
                    okHandler: (e) => {
                        EventBus.$emit('tracker:modal:reply', true)
                        EventBus.$emit('modal-away:hide')
                        EventBus.$emit("tracker:start")
                        EventBus.$emit("inactivity:start")
                    }
                }
            }
        },
        mounted() {

            EventBus.$on('away:trigger-modal', () => {
                EventBus.$emit('modal-away:show')
                EventBus.$emit("tracker:stop")
                EventBus.$emit("inactivity:reset")
                EventBus.$emit("inactivity:stop")
            })

            EventBus.$on('modal-away:show', (obj) => Event.$emit('modal-away:show', obj))
            EventBus.$on('modal-away:hide', () => Event.$emit('modal-away:hide'))
            Event.$on('modal-away:close', () => EventBus.$emit('modal-away:close'))

            this.showGenericModal = !timeTrackerInfo || +(timeTrackerInfo.patientId) === 0;
        }
    }
</script>

<style>
    .i-modal .modal-container {
        width: 500px;
    }
    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .i-modal .modal-container {
            width: 95%;
        }
    }
    .i-modal .modal-header {
        text-align: left;
        font-size: 20px;
    }

    .i-modal .modal-body {
        text-align: left;
        line-height: 25px;
        font-size: 18px;
    }

    .i-modal .modal-cancel-button {
        float: right;
    }

    .i-modal .modal-button {
        display: inline-block;
        margin-bottom: 0;
        font-weight: normal;
        text-align: center;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        background-image: none;
        border: 1px solid transparent;
        white-space: nowrap;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857;
        border-radius: 4px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        text-shadow: none;
        margin-right: 5px;
    }

    .i-modal .modal-cancel-button {
        color: #fff;
        background-color: #f0ad4e;
    }

    .i-modal .modal-cancel-button {
        color: #fff;
        background-color: #f0ad4e;
        float: initial;
    }

    .i-modal .modal-ok-button {
        color: #fff;
        background-color: #5cb85c;
        float: right;
    }

    .i-modal .modal-mask {
        background: black !important;
    }
</style>
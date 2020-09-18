<template>
    <span>
        <ul class="nav nav-pills bhi-nav">
            <li class="nav-item" :class="{ active: !isManualBehavioral }" v-if="isCcm">
                <a class="nav-link" data-toggle="tab" role="tab" title="switch to CCM mode" @click="setBhi(false)">CCM</a>
            </li>
            <li class="nav-item" :class="{ active: isManualBehavioral }" v-if="isBhi">
                <a class="nav-link" data-toggle="tab" role="tab" title="switch to BHI mode" @click="setBhi(true)">BHI</a>
            </li>
        </ul>

        <!--begin modal-->
        <modal name="bhi" class="modal-bhi" ok-text="Proceed" :no-cancel="true" :no-wrapper-close="true" :info="bhiModalInfo">
            <template slot="title" slot-scope="props">
                <h3 class="text-center">
                    Dual Behavioral Health &amp; CCM Patient
                </h3>
            </template>
            <template slot-scope="props">
                <div class="text-center">
                    <p>
                        Please use the selector at the top of the page to indicate if you are doing Chronic Care or Behavioral Health Management.
                    </p>
                </div>
            </template>
            <template slot="footer" slot-scope="props">
                <div class="text-center">
                    <label>
                        <input type="checkbox" style="display: inline-block" v-model="dontShowModalAgain" /> Don't show this message again
                    </label>
                </div>
            </template>
        </modal>
        <!--end modal-->
    </span>
</template>

<script>
    import { Event } from 'vue-tables-2'
    import EventBus from './event-bus'
    import { rootUrl } from '../../../app.config'
    import Modal from '../../../../../../../SharedVueComponents/Resources/assets/js/admin/common/modal'
    import stor from '../../../stor'

    export default {
        props: {
            userId: String,
            isBhi: {
                type: Boolean,
                default: false
            },
            isCcm: Boolean,
            isManualBehavioral: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            storeKey () {
                return `bhi-modal:${this.userId}:do-not-show`
            }
        },
        data () {
            const $vm = this
            return {
                dontShowModalAgain: false,
                bhiModalInfo: {
                    okHandler () {
                        if ($vm.dontShowModalAgain) {
                            stor.add($vm.storeKey, 'true')
                        }
                        Event.$emit('modal-bhi:hide')
                    }
                }
            }
        },
        components: {
            Modal
        },
        methods: {
            setBhi (mode) {
                EventBus.$emit('tracker:bhi:switch', mode)
            },
            showModal () {
                if (this.isCcm && this.isBhi && !stor.contains(this.storeKey)) {
                    Event.$emit('modal-bhi:show')
                }
            }
        },
        mounted () {
            console.log('isBhi', this.isBhi)
            console.log('isCcm', this.isCcm)

            this.showModal()

            Event.$on('bhi-switch:modal-bhi:show', this.showModal)
        }
    }
</script>

<style>
    ul.bhi-nav {
        display: inline-block;
        color: #50b2e2;
        border: 3px solid #50b2e2;
        position: relative;
        top: 10px;
        right: -7px;
    }

    ul.bhi-nav li {
        cursor: pointer;
        padding: 0px
    }

    ul.bhi-nav li a:hover {
        background-color: transparent;
    }

    ul.bhi-nav li.nav-item.active, ul.bhi-nav li.nav-item.active a {
        background-color: #50b2e2;
    }

    .modal-bhi .modal-container {
        width: 600px;
    }

    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .modal-bhi .modal-container {
            width: 95%;
        }
    }

    .modal-bhi .modal-footer {
        padding: 0px;
        font-size: 16px;
        color: #444;
    }

    .modal-bhi .modal-body {
        font-size: 20px;
        line-height: 30px;
    }
    
    .modal-button {
        display: inline-block;
        margin-bottom: 0;
        margin-left: 5px;
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
    }

    .modal-ok-button {
        background: #47beab;
        border: #005a47;
        color: white;
    }
</style>
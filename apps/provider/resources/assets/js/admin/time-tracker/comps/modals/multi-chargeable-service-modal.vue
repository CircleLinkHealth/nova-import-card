<template>
    <modal name="multi-chargeable-service" class="modal-multi-chargeable-service" ok-text="Proceed" :no-cancel="true"
           :no-wrapper-close="true" :info="options">
        <template slot="title" slot-scope="props">
            <h3 class="text-center">
                {{ getTitle() }}
            </h3>
        </template>
        <template slot-scope="props">
            <div class="text-center">
                <p>
                    {{ getBody() }}
                </p>
            </div>
        </template>
        <template slot="footer" slot-scope="props">
            <div class="text-center">
                <label>
                    <input type="checkbox" style="display: inline-block" v-model="dontShowModalAgain"/>
                    Don't show this message again
                </label>
            </div>
        </template>
    </modal>
</template>

<script>
import Modal from '../../../common/modal';
import stor from "../../../../stor";
import {Event} from "vue-tables-2";

const CCM = 'CCM';
const RPM = 'RPM';
const BHI = 'BHI';

export default {
    name: "multi-chargeable-service-modal",
    components: {
        Modal
    },
    props: [
        'storeKey',
        'chargeableServices'
    ],
    data() {
        const $vm = this
        return {
            dontShowModalAgain: false,
            options: {
                okHandler() {
                    if ($vm.dontShowModalAgain) {
                        stor.add($vm.storeKey, 'true')
                    }
                    Event.$emit('modal-multi-chargeable-service:hide')
                }
            }
        }
    },
    methods: {
        getTitle() {
            const len = this.chargeableServices.length;
            if (len === 2) {
                const names = this.chargeableServices.map(cs => this.getFriendlierTitle(cs.chargeable_service.display_name));
                return `Dual ${names[0]} & ${names[1]} Patient`;
            }
            return `Multi Services Patient`;
        },

        getFriendlierTitle(csFriendlyName) {
            switch (csFriendlyName) {
                case CCM:
                    return 'Chronic Care';
                case BHI:
                    return 'Behavioral Health Management';
                case RPM:
                    return 'Remote Patient Monitoring';
                default:
                    return csFriendlyName;
            }
        },

        getBody() {
            const names = this.chargeableServices.map(cs => this.getFriendlierDescription(cs.chargeable_service.display_name));
            let joined = '';
            names.forEach((n, i) => {
                if (i === names.length - 1) {
                    joined += n;
                } else if (i === names.length - 2) {
                    joined += `${n} or `;
                } else {
                    joined += `${n}, `;
                }
            });
            return `Please use the selector at the top of the page to indicate if you are doing ${joined}.`;
        },

        getFriendlierDescription(csFriendlyName) {
            switch (csFriendlyName) {
                case CCM:
                    return 'Chronic Care';
                case BHI:
                    return 'Behavioral Health';
                case RPM:
                    return 'Remote Patient Monitoring';
                default:
                    return csFriendlyName;
            }
        }
    }
}
</script>

<style>
.modal-multi-chargeable-service .modal-container {
    width: 600px;
}

@media only screen and (max-width: 768px) {
    /* For mobile phones: */
    .modal-multi-chargeable-service .modal-container {
        width: 95%;
    }
}

.modal-multi-chargeable-service .modal-footer {
    padding: 0px;
    font-size: 16px;
    color: #444;
}

.modal-multi-chargeable-service .modal-body {
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

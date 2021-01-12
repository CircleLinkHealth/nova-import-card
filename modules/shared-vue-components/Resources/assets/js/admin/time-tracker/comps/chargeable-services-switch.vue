<template>
    <div>
        <ul class="nav nav-pills bhi-nav">
            <li class="nav-item" v-for="csSummary in chargeableServices"
                :class="{ active: csSummary.chargeable_service.id === chargeableServiceId }">
                <a class="nav-link" data-toggle="tab" role="tab"
                   :title="`switch to ${csSummary.chargeable_service.display_name} mode`"
                   @click="onClick(csSummary.chargeable_service.id)">
                    {{ csSummary.chargeable_service.display_name }}
                </a>
            </li>
        </ul>

        <multi-chargeable-service-modal v-if="hasMoreThanOneChargeableService"
                                        :store-key="modalStoreKey"
                                        :chargeable-services="chargeableServices">
        </multi-chargeable-service-modal>

    </div>
</template>

<script>
import EventBus from "./event-bus";
import MultiChargeableServiceModal from './modals/multi-chargeable-service-modal';
import stor from "../../../stor";
import {Event} from "vue-tables-2";

export default {
    name: "chargeable-services-switch",
    props: [
        'chargeableServices',
        'chargeableServiceId'
    ],
    components: {
        MultiChargeableServiceModal
    },
    data() {
        return {
            modalStoreKey: 'modal-multi-chargeable-service'
        }
    },
    methods: {
        onClick(csId) {
            EventBus.$emit('tracker:chargeable-service:switch', csId);
        },
        showModal() {
            if (!stor.contains(this.modalStoreKey)) {
                Event.$emit('modal-multi-chargeable-service:show');
            }
        }
    },
    computed: {
        hasMoreThanOneChargeableService() {
            return this.chargeableServices.filter(cs => cs.chargeable_service.id > -1).length > 1;
        }
    },
    mounted() {
        this.showModal();
    }
}
</script>

<style scoped>
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
</style>

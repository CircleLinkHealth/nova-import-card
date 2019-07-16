<template>
    <!-- Modal -->
    <div class="call-assistance-modal">
        <div class="content">
            <div class="header">
                <mdb-btn @click="handleClick" class="btn-transparent">
                    <font-awesome-icon icon="angle-left" size="3x"></font-awesome-icon>
                </mdb-btn>
                <span class="title">Call</span>
            </div>
            <br>
            <div class="body">
                Request Call Assistance
                <br/>
                {{phoneNumber}}
                <mdb-btn size="sm">
                    <font-awesome-icon icon="phone-alt"></font-awesome-icon>
                </mdb-btn>

            </div>
        </div>
    </div>

</template>

<script>

    import {mdbBtn} from 'mdbvue';
    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faAngleLeft, faPhoneAlt} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

    library.add(faAngleLeft, faPhoneAlt);

    export default {
        name: "callAssistance",
        components: {FontAwesomeIcon, mdbBtn},
        props: ['phoneNumber'],
        data() {
            return {}
        },

        methods: {
            handleClick() {
                this.$emit('closeCallAssistanceModal')
            }
        },
    }

</script>

<style scoped>
    .call-assistance-modal {
        width: 250px;
        height: 250px;
        box-shadow: 0 0 5px 0 rgba(0, 0, 0, 0.1);
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
        margin-left: 2%;
        margin-bottom: 1%;
    }

    .header {
        width: 248px;
        height: 50px;
        box-shadow: 0 0 5px 0 #50b2e2;
        border: solid 1px #50b2e2;
        background-color: #50b2e2;
    }

    .title {
        font-family: Poppins, serif;
        letter-spacing: 1px;
        color: #ffffff;
    }

    .body {
        width: 260px;
        height: 46px;
        font-family: Poppins, serif;
        font-size: 14px;
        letter-spacing: 0.8px;
        padding-left: 15px;
        color: #1a1a1a;
    }

    .body .text-style-1 {
        font-weight: 600;
        color: #50b2e2;
    }

    .fa-angle-left {
        color: #ffffff;
    }

    .btn-transparent {
        background-color: transparent !important;
        padding: 0 10px;
        box-shadow: none;
    }
</style>

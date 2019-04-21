<template>
    <!-- Modal -->
    <div class="call-assistance-modal">
        <div class="content">
            <div class="header">
                <button type="button"
                        id="close-call-assistance"
                        class="btn btn-sm"
                        @click="handleClick">
                    <i class="fas fa-angle-left"></i>
                </button>
                <span class="title">Call</span>
            </div>
            <br>
            <div class="body">
                Request Call Assistance
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        name: "callAssistance",
        data() {
            return {

            }
        },

        methods: {
            handleClick(){
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

    .btn-sm {
        padding-top: 12px;
    }

    .title {
        font-family: Poppins;
        letter-spacing: 1px;
        color: #ffffff;
    }

    .body {
        width: 260px;
        height: 46px;
        font-family: Poppins;
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
        width: 20px;
        height: 30px;
        color: #ffffff;
    }
</style>
<template>
    <transition name="vue-modal">
        <div class="vue-modal-mask">
            <div class="vue-modal-wrapper">
                <div class="vue-modal-container">
                    <div class="vue-modal-header">
                        <slot name="header">
                        </slot>
                    </div>
                    <div class="vue-modal-body">
                        <slot name="body">
                        </slot>
                    </div>
                    <div class="vue-modal-footer">
                        <slot name="footer">
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
    export default {}
</script>

<style scoped>
    .close-button {
        font-size: 25px;
        font-weight: 100;
        color: tomato;
        position: absolute;
        top: 15px;
        right: 25px;
        cursor: pointer;
    }

    .vue-modal-mask {
        position: fixed;
        z-index: 1050;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
    }

    .vue-modal-wrapper {
        display: table-cell;
        vertical-align: middle;
    }

    @media ( min-width: 768px) {
        .vue-modal-container {
            width: 50%;
            min-width: 430px;
        }
    }

    .vue-modal-container {
        margin: 0 auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        position: relative;
        overflow-y: auto;
        max-height: 100vh;
    }

    .vue-modal-header h3 {
        margin: 0;
    }

    .vue-modal-header, .vue-modal-footer {
        border: none;
    }

    .vue-modal-default-button {
        float: right;
    }

    .vue-modal-body {
        padding: 5px;
    }

    /*
     * the following styles are auto-applied to elements with
     * v-transition="modal" when their visiblity is toggled
     * by Vue.js.
     *
     * You can easily play with the modal transition by editing
     * these styles.
     */

    .vue-modal-enter {
        opacity: 0;
    }

    .vue-modal-leave-active {
        opacity: 0;
    }

    .vue-modal-enter .vue-modal-container,
    .vue-modal-leave-active .vue-modal-container {
        -webkit-transform: scale(1.3);
        transform: scale(1.3);
    }
</style>
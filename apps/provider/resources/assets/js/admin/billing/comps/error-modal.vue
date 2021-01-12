<template>
    <modal name="error" :info="errorModalInfo" :no-footer="true" :no-cancel="true" :no-title="true" class-name="modal-error">
    </modal>
</template>

<script>
    import { Event } from 'vue-tables-2'
    import Modal from '../../common/modal'

    export default {
        name: 'error-modal',
        components: {
            'modal': Modal
        },
        data() {
            return {
                errorModalInfo: {
                    okHandler() {
                        Event.$emit("modal-error:hide")
                        if (this.done && typeof(this.done) === 'function') {
                          this.done(this)
                        }
                    }
                }
            }
        },
        mounted() {
            Event.$on('modal-error:show', (modal, done) => {
                if (done && typeof(done) == 'function') this.errorModalInfo.done = done.bind(this.errorModalInfo)
          })
        }
    }
</script>

<style>
    .error-body {
        font-family: "calibri";
    }

    .modal-error .modal-container {
        color: red;
    }

    .modal-error button,input[type='button'] {
        background-color: transparent;
        color: red;
        border-color: #999;
    }
</style>
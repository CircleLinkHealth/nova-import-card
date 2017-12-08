<template>
    <modal name="error" :info="errorModalInfo" :no-footer="true" class-name="modal-error">
        <template slot="title" scope="props"><div>Error Details</div></template>
        <template scope="props">
        <div class="row">
          <div class="col-sm-12 error-body">
            
          </div>
        </div>
      </template>
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
            Event.$on('modal-error:show', (done) => {
                if (done && typeof(done) == 'function') this.errorModalInfo.done = done.bind(this.errorModalInfo)
          })
        }
    }
</script>

<style>
    .error-body {
        font-family: "calibri";
    }
</style>
<template>
    <modal ref="attest-call-conditions-modal" name="attest-call-conditions-modal" :no-title="true" :no-footer="true"
           :no-cancel="true" :no-buttons="true"
           class-name="modal-attest-call-conditions">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <h4>TEST</h4>
                    </div>
                </div>
            </div>
        </template>
    </modal>
</template>
<script>
    import Modal from '../admin/common/modal';

    export default {
        name: "attest-call-conditions-modal",
        components: {
            'modal': Modal,
        },
        mounted() {
            App.$on('show-attest-call-conditions-modal', () => {
                console.log('component reached');
                this.$refs['attest-call-conditions-modal'].visible = true;
            });
        }
    }
</script>

<style scoped>

</style>
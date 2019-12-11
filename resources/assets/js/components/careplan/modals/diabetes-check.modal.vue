<template>
    <modal ref="diabetes-check-modal" name="diabetes-check" :no-title="true" :no-footer="true" :no-cancel="true"
           :no-buttons="true"
           class-name="modal-diabetes-check">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12" style="text-align: center"><h4><span style="color: red">Warning!</span></h4></div>
                <div class="col-sm-12" style="font-size: 16px !important;">
                    <span>This patient has both Diabetes Type 1 and Type 2. Please confirm that this is correct, otherwise
                        choose the correct Diabetes type and try again.</span>
                </div>
                <div class="col-sm-12 text-right top-20">
                    <button v-on:click="hideModal()" type="button" class="btn btn-danger">Edit Problems</button>
                    <button v-on:click="hideAndSubmitForm()" type="button" class="btn btn-primary right-0">Confirm and
                        Approve
                    </button>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import Modal from '../../../admin/common/modal'

    export default {
        name: "diabetes-check-modal",
        components: {
            'modal': Modal,
        },
        methods: {
            hideModal() {
                this.$refs['diabetes-check-modal'].visible = false;
            },
            hideAndSubmitForm() {
                App.$emit('confirm-diabetes-conditions');
                this.hideModal();
            }
        },
        mounted() {
            App.$on('show-diabetes-check-modal', () => {
                this.$refs['diabetes-check-modal'].visible = true;
            });
        }
    }
</script>

<style>
    .modal-diabetes-check .modal-container {
        width: 50%;
    }
</style>
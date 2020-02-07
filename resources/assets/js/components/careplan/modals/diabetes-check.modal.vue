<template>
    <modal ref="diabetes-check-modal" name="diabetes-check" :no-title="true" :no-footer="true" :no-cancel="true"
           :no-buttons="true"
           class-name="modal-diabetes-check">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12 warning-text"><h4><span>Warning!</span></h4></div>
                <div class="col-sm-12 modal-text" >
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
    import {Event} from 'vue-tables-2'

    export default {
        name: "diabetes-check-modal",
        components: {
            'modal': Modal,
        },
        methods: {
            hideModal() {
                this.$refs['diabetes-check-modal'].visible = false;
                Event.$emit('modal-care-areas:show')
            },
            hideAndSubmitForm() {
                App.$emit('confirm-diabetes-conditions');
                this.$refs['diabetes-check-modal'].visible = false;
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

    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .modal-diabetes-check .modal-container {
            width: 95%;
        }
    }

    .modal-diabetes-check .modal-footer {
        padding: 0px;
    }

    .btn.btn-secondary {
        background-color: #ddd;
        padding: 10 20 10 20;
        margin-right: 15px;
        margin-bottom: 5px;
    }

    .btn.btn-danger {
        background-color: #d9534f;
    }

    .top-20 {
        margin-top: 20px;
    }

    .top-30 {
        margin-top: 30px;
    }

    .absolute {
        position: absolute;
    }


    .warning-text {
        font-size: 30px !important;
        text-align: center;
        color: red;
    }
    .modal-text {
        font-size: 20px;
    }


</style>
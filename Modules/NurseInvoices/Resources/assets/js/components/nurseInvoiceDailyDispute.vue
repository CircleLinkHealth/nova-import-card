<template>
    <div @mouseover="mouseOver" @mouseleave="mouseLeave">
        {{this.formattedTime}}
        <span v-show="editButtonActive"
              @click="handleEdit()"
              aria-hidden="true"
              class="edit-button">
           <i class="glyphicon glyphicon-pencil"></i> Edit
        </span>

        <span v-show="showDisputeBox"
              aria-hidden="true"
              class="dispute-box">
            <input type="text" placeholder="Time in minutes">
            <button type="submit">Save</button>
        </span>

        <div v-show="showDisputeBox"
             class="dismiss"
             @click="dismiss()">
            <i class="glyphicon glyphicon-remove"></i>
        </div>

        <span v-show="deleteButtonActive"
              @click="handleDelete()"
              aria-hidden="true"
              class="delete-button">
           <i class="glyphicon glyphicon-erase"></i> Delete
        </span>
    </div>
</template>

<script>
    export default {
        props: ['formattedTime'],
        name: "nurseInvoiceDailyDispute",

        data() {
            return {
                editButtonActive: false,
                deleteButtonActive: false,
                showDisputeBox: false,
            }
        },

        methods: {
            mouseOver() {
                if (this.showDisputeBox === false) {
                    this.editButtonActive = true;
                    this.deleteButtonActive = true;
                }

            },
            mouseLeave() {
                this.editButtonActive = false;
                this.deleteButtonActive = false;
            },
            handleEdit() {
                this.editButtonActive = false;
                this.deleteButtonActive = false;
                this.showDisputeBox = true;
            },
            handleDelete() {

            },
            dismiss() {
                this.editButtonActive = false;
                this.deleteButtonActive = false;
                this.showDisputeBox = false;
            },

        },
    }
</script>

<style scoped>
    .edit-button {
        color: #87cefa;
        padding-left: 36%;
    }

    .delete-button {
        color: #ff0000;
        padding-left: 3%;
    }

    .dispute-box {

    }
    .dismiss{
        color: #ff0000;
    }
</style>


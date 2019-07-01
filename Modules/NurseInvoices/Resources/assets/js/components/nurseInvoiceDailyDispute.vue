<template>
    <div @mouseover="mouseOver" @mouseleave="mouseLeave">
       <span :class="{strike: strikethroughTime}">
           {{this.formattedTime}}
       </span>

        <span v-show="disputeRequestedTime"
              class="dispute-requested-time">
            {{this.requestedTime}}
        </span>

        <span v-show="editButtonActive"
              @click="handleEdit()"
              aria-hidden="true"
              class="edit-button">
           <i class="glyphicon glyphicon-pencil"></i> Edit
        </span>

        <span v-show="showDisputeBox"
              aria-hidden="true"
              class="dispute-box">
                        <input type="text"
                               class="text-box"
                               placeholder="hh:mm"
                               v-model="requestedTime">

            <span class="save"
                  @click="saveDispute">
                <i class="glyphicon glyphicon-saved"></i>
            </span>

        </span>

        <span v-show="showDisputeBox"
              class="dismiss"
              @click="dismiss()">
            <i class="glyphicon glyphicon-remove"></i>
        </span>

        <span v-show="deleteButtonActive && disputeRequestedTime"
              @click="handleDelete()"
              aria-hidden="true"
              class="delete-button">
           <i class="glyphicon glyphicon-erase"></i> Delete
        </span>
    </div>
</template>

<script>
    export default {
        props: [
            'invoiceData',
            'invoiceId',
            'disputedDay'
        ],
        name: "nurseInvoiceDailyDispute",

        data() {
            return {
                editButtonActive: false,
                deleteButtonActive: false,
                showDisputeBox: false,
                formattedTime: this.invoiceData.formatted_time,
                requestedTime: '',
                disputeRequestedTime: false,
                strikethroughTime: false,
            }
        },

        computed: {},


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
                this.disputeRequestedTime = false;
                this.strikethroughTime = false;
                //    @todo:connect to back end
            },
            dismiss() {
                this.editButtonActive = false;
                this.showDisputeBox = false;

                if (!this.disputeRequestedTime) {
                    this.deleteButtonActive = false;
                }
            },
            saveDispute() {
                //    @todo:add loader
                if (this.requestedTime.length !== 0) {
                    axios.post('/nurseinvoices/daily-dispute', {
                        invoiceId: this.invoiceId,
                        suggestedFormattedTime: this.requestedTime,
                        disputedFormattedTime: this.formattedTime,
                        disputedDay: this.disputedDay,
                    })
                        .then((response) => {
                            console.log(response.data);
                            this.disputeRequestedTime = true;
                            this.strikethroughTime = true;
                            this.dismiss();
                        })
                        .catch((error) => {
                            console.log(error);
                            if (error.response && error.response.status === 404) {
                                this.error = "Not Found [404]";
                            } else if (error.response && error.response.status === 419) {
                                this.error = "Not Authenticated [419]";
                            } else if (error.response && error.response.data) {
                                const errors = [error.response.data.message];
                                Object.keys(error.response.data.errors || []).forEach(e => {
                                    errors.push(error.response.data.errors[e]);
                                });
                                this.error = errors.join('<br/>');
                            } else {
                                this.error = error.message;
                            }
                        });
                }
            },
        },

    }

</script>

<style scoped>
    .edit-button {
        color: #87cefa;
        padding-left: 10%;
    }

    .delete-button {
        color: #ff0000;
        padding-left: 3%;
    }

    .dispute-box {
        padding-left: 14%;
    }

    .dismiss {
        color: #ff0000;
        padding-left: 2%;
    }

    .save {
        color: #32CD32;
        padding-left: 2%;
    }

    .text-box {
        max-width: 24%;
    }

    .strike {
        text-decoration: line-through;
        color: #ff0000;
    }

    .dispute-requested-time {
        padding-left: 3%;
    }

</style>


<template>
    <div>
        <div @mouseover="mouseOver" @mouseleave="mouseLeave">

            <!--Original Formatted Value from NurseInvoice-->
            <span :class="{strike: strikethroughTime || setStrikeThrough}">
           {{this.formattedTime}}
            <loader v-show="loader"></loader>
       </span>

            <span v-show="showLiveRequestedTime && !hideTillRefresh"
                  class="dispute-requested-time">
            {{this.liveRequestedTime}}
        </span>

            <span v-show="!showLiveRequestedTime && !hideTillRefresh"
                  class="dispute-requested-time">
            {{this.requestedTimeFromDb}}
        </span>

            <!--Edit Btn-->
            <span v-show="editButtonActive"
                  @click="handleEdit()"
                  aria-hidden="true"
                  class="edit-button">
           <i class="glyphicon glyphicon-pencil"></i> Edit
        </span>

            <!--Delete Btn-->
            <span v-show="showDeleteBtn && !hideTillRefresh"
                  @click="handleDelete()"
                  aria-hidden="true"
                  class="delete-button">
           <i class="glyphicon glyphicon-erase"></i> Delete
        </span>

            <!--Input for new time hh:mm with save & dismiss btn-->
            <span v-show="showDisputeBox"
                  aria-hidden="true"
                  class="dispute-box">
                        <input type="number"
                               class="text-box"
                               placeholder="hh:mm"
                               v-model="liveRequestedTime">

            <span class="save"
                  @click="saveDispute">
                <i class="glyphicon glyphicon-saved"></i>
            </span>

                    <span v-show="showDisputeBox"
                          class="dismiss"
                          @click="dismiss()">
            <i class="glyphicon glyphicon-remove"></i>
        </span>
            </span>
        </div>
    </div>
</template>

<script>
    import LoaderComponent from '../../../../../../resources/assets/js/components/loader'
    import {mapActions} from 'vuex'
    import {addNotification} from '../../../../../../resources/assets/js/store/actions'

    export default {
        props: [
            'invoiceData',
            'invoiceId',
            'day',
        ],

        components: {
            'loader': LoaderComponent,
            'addNotification': addNotification,
        },

        name: "nurseInvoiceDailyDispute",

        data() {
            return {
                editButtonActive: false,
                deleteButtonActive: false,
                showDisputeBox: false,
                formattedTime: this.invoiceData.formatted_time,
                liveRequestedTime: '',
                requestedTimeFromDb: this.invoiceData.suggestedTime,
                disputeRequestedTime: false,
                strikethroughTime: false,
                //these are used to force a behavior on an element
                // eg. show/hide till user refreshes page so component can load the
                //newly created data from DB.
                showTillRefresh: true,
                hideTillRefresh: false,
                //
                loader: false,
                errors: [],
            }
        },

        computed: {
            showLiveRequestedTime() {
                const requestedTimeFromDbExists = this.requestedTimeFromDb === undefined;
                return !!this.liveRequestedTime || requestedTimeFromDbExists;
            },

            showDeleteBtn() {
                return !!(this.disputeRequestedTime && this.deleteButtonActive)
                    || (this.deleteButtonActive
                        && this.showLiveRequestedTime === false)
            },

            setStrikeThrough() {
                return this.showTillRefresh && (this.requestedTimeFromDb === undefined && this.strikethroughTime
                    || this.requestedTimeFromDb !== undefined && !this.strikethroughTime);

            }
        },

        methods: Object.assign(mapActions(['addNotification']), {
            mouseOver() {
                if (!this.showDisputeBox) {
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
                this.loader = true;
                axios.delete(`/nurseinvoices/delete-dispute/${this.invoiceId}/${this.day}`, {
                    'invoiceId': this.invoiceId,
                    'day': this.day,

                })
                    .then((resposne) => {
                        this.disputeRequestedTime = false;
                        this.strikethroughTime = false;
                        this.showTillRefresh = false;
                        this.hideTillRefresh = true;
                        this.loader = false;

                        this.addNotification({
                            title: "Deleted",
                            text: "Your dispute has been submitted deleted",
                            type: "info",
                            timeout: true
                        });
                    })
                    .catch((error) => {
                        console.log(error);
                        if (error.response && error.response.status === 404) {
                            this.error = "Not Found [404]";
                        } else if (error.response && error.response.status === 419) {
                            this.error = "Not Authenticated [419]";
                        } else {
                            this.error = error.message;
                        }
                    });
            },
            dismiss() {
                this.editButtonActive = false;
                this.showDisputeBox = false;

                if (!this.disputeRequestedTime) {
                    this.deleteButtonActive = false;
                }
            },
            saveDispute() {
                this.loader = true;
                axios.post('/nurseinvoices/daily-dispute', {
                    invoiceId: this.invoiceId,
                    suggestedFormattedTime: this.liveRequestedTime,
                    disputedFormattedTime: this.formattedTime,
                    disputedDay: this.day,
                })
                    .then((response) => {
                        this.deleteButtonActive = true;
                        this.disputeRequestedTime = true;
                        this.strikethroughTime = true;
                        this.hideTillRefresh = false;
                        this.dismiss();
                        this.loader = false;

                        this.addNotification({
                            title: "Success!",
                            text: "Your dispute has been submitted. We\'ll get back to you as soon as possible.",
                            type: "success",
                            timeout: true
                        });
                    })
                    .catch((error) => {
                        console.log(error);
                        if (error.response && error.response.status === 404) {
                            this.error = "Not Found [404]";
                        } else if (error.response && error.response.status === 422) {
                            this.loader = false;
                            this.errors = error.response.data.errors;

                            this.addNotification({
                                title: "Warning!",
                                text: this.errors.suggestedFormattedTime[0],
                                type: "danger",
                                timeout: true
                            });

                        } else {
                            this.error = error.message;
                        }
                    });
            },
        }),

        created() {

        }

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


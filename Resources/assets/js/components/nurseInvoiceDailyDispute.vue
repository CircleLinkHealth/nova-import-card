<template>
    <div>
        <div @mouseover="mouseOver" @mouseleave="mouseLeave">

            <!--Original Formatted Value from NurseInvoice-->
            <span>
           {{this.formattedTime}}
            </span>

            <!--Requested Time From nurse-->
            <span v-if="showTillRefresh && !isInvalidated"
                  class="dispute-requested-time"
                  :class="{strike: showDisputeStatus === 'rejected'}">{{setRequestedValue}}</span>
            <span v-else="strikethroughSuggestedTime || isInvalidated"
                  class="invalidated">
                    <span data-tooltip="Your request cannot be accepted due to already having a bonus for this day">{{setRequestedValue}}</span>
                </span>

            <!--Status glyphicons-->
            <span v-if="showDisputeStatus !== false"
                  class="dispute-requested-time">
                <span v-if="showDisputeStatus === 'approved' && !isInvalidated"
                      data-tooltip="Your request has been approved"><i class="glyphicon glyphicon-ok-circle"></i></span>
               <span v-else-if="showDisputeStatus === 'rejected' && !isInvalidated"
                     data-tooltip="Your request has been rejected"><i
                       class="glyphicon glyphicon-remove-sign"></i></span>
                <span v-else-if="showDisputeStatus === 'pending' && !isInvalidated"
                      data-tooltip="Your request is pending."><i
                        class="glyphicon glyphicon-option-horizontal"></i></span>
        </span>

            <span style="float: right;"><loader v-show="loader" class="loader"></loader></span>
            <!--Edit Btn-->
            <span v-show="!isInvalidated
            && editButtonActive
            && isUserAuthToDailyDispute
            && canBeDisputed
            && isUserAuthToDailyDispute
            && (!showDisputeStatus || showDisputeStatus === 'pending')"
                  @click="handleEdit()"
                  aria-hidden="true"
                  class="edit-button">
           <i class="glyphicon glyphicon-pencil"></i>
        </span>

            <!--Delete Btn-->
            <span v-show="(showDeleteBtn && !isInvalidated && isUserAuthToDailyDispute && canBeDisputed && showTillRefresh)
            && (!showDisputeStatus || showDisputeStatus === 'pending')"
                  @click="handleDelete()"
                  aria-hidden="true"
                  class="delete-button">
           <i class="glyphicon glyphicon-erase"></i>
        </span>

            <!--Input for new time hh:mm with save & dismiss btn-->
            <span v-show="showDisputeBox"
                  aria-hidden="true"
                  class="dispute-box">
                        <input type="text"
                               class="text-box"
                               :class="{validation: !validateTime}"
                               placeholder="hh:mm"
                               v-model="liveRequestedTime">
                <!--Save Button-->
            <span class="save">
                <button class="button"
                        @click="saveDispute"
                        :class="{disable:disableButton}"
                        :disabled="disableButton">
                <i class="glyphicon glyphicon-saved"></i>
                </button>
            </span>
                <!--Text box-->
                <span class="dismiss">
                    <button v-show="showDisputeBox"
                            class="button"
                            @click="dismiss()">
                          <i class="glyphicon glyphicon-remove"></i>
                    </button>
                </span>
            </span>
        </div>
    </div>
</template>

<script>
    import LoaderComponent from '../../../../../SharedVueComponents/Resources/assets/js/components/loader'
    import {mapActions} from 'vuex'
    import {addNotification} from '../../../../../../resources/assets/js/store/actions'


    export default {
        props: [
            'invoiceData',
            'invoiceId',
            'day',
            'isUserAuthToDailyDispute',
            'canBeDisputed'
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
                formattedTime: this.invoiceData.disputedFormattedTime ? this.invoiceData.disputedFormattedTime : this.invoiceData.formatted_time,
                requestedTimeToShow: '',
                liveRequestedTime: '',
                requestedTimeFromDb: this.invoiceData.suggestedTime,
                userDisputedTime: false,
                strikethroughSuggestedTime: false,
                showTillRefresh: true,
                loader: false,
                errors: [],
                temporaryTime: '',
                disputeStatus: this.invoiceData.status,
                disputeInvalidated: this.invoiceData.invalidated,
            }
        },

        computed: {

            isInvalidated() {
                return !!this.disputeInvalidated;
            },

            requestedTimeIsVisible() {
                const requestedTimeFromDbExists = this.requestedTimeFromDb !== undefined;
                const liveRequestedTime = !!this.liveRequestedTime;
                return liveRequestedTime || requestedTimeFromDbExists;
            },

            setRequestedValue() {
                const requestedTimeFromDbExists = this.requestedTimeFromDb === undefined;
                const temporaryTime = !!this.temporaryTime;

               if (temporaryTime || requestedTimeFromDbExists){
                   const secondHrDigit = this.temporaryTime.substring(0, 2).charAt(1);
                   const timeWithLeadingZero = this.temporaryTime.padStart(5, '0'); //add leading zero to time

                   return secondHrDigit === ':' ? timeWithLeadingZero : this.temporaryTime;
               }

                return this.requestedTimeFromDb;
            },

            showDeleteBtn() {
                return !!this.deleteButtonActive && (this.userDisputedTime || this.requestedTimeIsVisible)

            },

            validateTime() {
                const inputValue = this.liveRequestedTime;
                const formatRule = inputValue.match('([0-9]|1[0-9]|2[0-3])(:[0-5][0-9])');

                if (this.liveRequestedTime.length === 0) {
                    return true;
                }
                return !!formatRule && this.liveRequestedTime.length <= 5;
            },

            disableButton() {
                return this.validateTime !== true || this.liveRequestedTime.length === 0;
            },

            showDisputeStatus() {
                if (this.disputeStatus === undefined) {
                    return false;
                } else if (this.disputeStatus === 'approved') {
                    return 'approved';
                } else if (this.disputeStatus === 'rejected') {
                    return 'rejected';
                } else if (this.disputeStatus === 'pending') {
                    return 'pending';
                }
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
                        this.userDisputedTime = false;

                        this.showTillRefresh = false;
                        this.requestedTimeFromDb = undefined;
                        this.liveRequestedTime = '';
                        this.temporaryTime = '';
                        this.disputeStatus = undefined;
                        this.loader = false;

                        this.addNotification({
                            title: "Deleted",
                            text: "Your dispute has been deleted",
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
            },
            saveDispute() {
                const defaultDisputeStatus = 'pending';
                this.loader = true;
                axios.post('/nurseinvoices/daily-dispute', {
                    invoiceId: this.invoiceId,
                    suggestedFormattedTime: this.liveRequestedTime,
                    disputedFormattedTime: this.formattedTime,
                    disputeStatus: defaultDisputeStatus,
                    disputedDay: this.day,
                })
                    .then((response) => {
                        this.deleteButtonActive = true;
                        this.userDisputedTime = true;
                        this.showTillRefresh = true;
                        this.editButtonActive = false;
                        this.showDisputeBox = false;
                        this.disputeStatus = defaultDisputeStatus;
                        this.temporaryTime = this.liveRequestedTime;
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
        padding-left: 22%;
    }

    .delete-button {
        color: #ff0000;
        padding-left: 8%;
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
        margin-left: -13%;
    }

    .validation {
        border-color: rgba(255, 0, 0, 0.17);
        background-color: rgba(255, 0, 0, 0.17);
    }

    .strike {
        text-decoration: line-through;
        color: #ff0000;
    }

    .button {
        background-color: Transparent;
        background-repeat: no-repeat;
        border: none;
        cursor: pointer;
        overflow: hidden;
        outline: none;
    }

    .dispute-requested-time {
        padding-left: 3%;
    }

    .disable {
        background-color: #f4f6f6;
        color: #d5dbdb;
        cursor: default;
        opacity: 0.7;
    }

    .invalidated {
        text-decoration: line-through;
        color: skyblue;
        padding-left: 3%;
    }

    .glyphicon-pencil {
        float: right;
        margin-right: 5%;
    }

    .glyphicon-erase {
        float: right;
        margin-right: 5%;
    }

    .glyphicon-option-horizontal {
        color: rgb(0, 191, 255);
        margin-right: 1%;
    }

    .glyphicon-remove-sign {
        margin-right: 1%;
        color: #ff0000;
    }

    .glyphicon-ok-circle {
        margin-right: 1%;
        color: #008000;
    }

    .loader {
        width: 17px;
        height: 17px;
    }

    .strike {
        text-decoration: line-through;
        color: #ff0000;
    }

</style>


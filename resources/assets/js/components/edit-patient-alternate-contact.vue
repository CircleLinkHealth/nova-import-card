<template>
    <div class="input-group">

        <div>
            <loader v-if="loading"></loader>
        </div>

        <div v-if="!loading" class="alternate-fields">
            <span v-if="!loading && shouldDisplayHelpText"
            class="help-block"
            title="Missing alternate contact details(optional)."
            style="color: #50b2e2; font-size: 15px; cursor: pointer"
            @click="showAlternateFields">
                {{helperText}}
            </span>

            <div v-if="enableAlternateFields">
                <input name="alternativeContactName"
                       class="form-control alternative-field"
                       maxlength="40"
                       minlength="3"
                       type="text"
                       title="Type alternate contact name"
                       placeholder="Alternate contact name"
                       v-model="alternateContactDetails[0].agentName"
                       :disabled="loading"/>

                <input name="alternativeEmail"
                       class="form-control alternative-field"
                       type="text"
                       title="Type alternate contact email"
                       placeholder="Alternate contact email"
                       v-model="alternateContactDetails[0].agentEmail"
                       :disabled="loading"/>
                <br>
                <input name="alternativeRelationship"
                       class="form-control alternative-field"
                       maxlength="20"
                       minlength="3"
                       type="text"
                       title="Type alternate contact relationship"
                       placeholder="Alternate contact relationship"
                       v-model="alternateContactDetails[0].agentRelationship"
                       :disabled="loading"/>

                <div class="alt-phone-number">
                   <span class="input-group-addon plus-one">
                       +1
                   </span>
                    <input name="number"
                           class="form-control phone-number"
                           type="tel"
                           maxlength="10"
                           placeholder="5417543120"
                           v-model="alternateContactDetails[0].agentTelephone.number"
                           :disabled="loading"/>
                </div>
            </div>

            <div v-if="!loading" class="alt-btns">
                <br>
                <button v-if="alternateSaveBtnIsVisible"
                        class="btn btn-success btn-sm save-alt-contact"
                        type="button"
                        @click="saveNewAlternateNumberAndContactDetails"
                        :disabled="loading || disableAltSaveButton">
                    Save alternate contact
                </button>

                <button v-if="alternateClearBtnIsVisible"
                        class="btn btn-danger btn-sm delete-alt-contact"
                        type="button"
                        @click="deleteAlternateContact(false)"
                        :disabled="loading || ! alternateClearBtnIsVisible">
                    Clear alternate contact
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import axios from "../bootstrap-axios";
import LoaderComponent from "./loader";
import EventBus from '../admin/time-tracker/comps/event-bus';

export default {

    name: "edit-patient-alternate-contact",

    components: {
        'loader': LoaderComponent,
    },

    props:[
        'userId',
        'callEnabled',
        'altContact',
    ],
    data(){
        return {
            loading:false,
            helperTextClicked:false,
            initialAlternatePhoneSavedInDB:'',
            initialAlternateEmailSavedInDB:'',
            initialAlternateRelationshipSavedInDB:'',
            initialAlternateNameSavedInDB:'',
            alternateContactDetails:[
                {
                    agentEmail:'',
                    agentName:'',
                    agentRelationship:'',
                    agentTelephone:[],
                }
            ],
        }
},

    computed:{
        enableAlternateFields(){
            return ! this.callEnabled || this.helperTextClicked;
        },

        helperText(){
            if(this.alternatePhoneIsEmpty && (! this.alternateNameIsEmpty
                || ! this.alternateRelationshipIsEmpty
                || ! this.alternateEmailIsEmpty)){
                return this.helperTextClicked ? 'Hide alternate contact'
                    : 'Add missing alternate contact phone number';
            }

            if (this.anyAlternateFieldIsEmpty){
                return this.helperTextClicked ? 'Hide alternate contact'
                    : 'Add missing alternate contact details';
            }

            return this.helperTextClicked ? 'Hide alternate contact'
                : 'Show alternate contact details';


        },

        alternateClearBtnIsVisible(){
            if (! this.alternateEmailHasNotChanged && this.initialAlternateEmailSavedInDB.length !== 0){
                return true;
            }

            if (! this.alternateNameHasNotChanged && this.initialAlternateNameSavedInDB.length !== 0){
                return true;
            }

            if (! this.alternatePhoneHasNotChanged && this.initialAlternatePhoneSavedInDB.length !== 0){
                return true;
            }

            if (! this.alternateRelationshipHasNotChanged && this.initialAlternateRelationshipSavedInDB.length !== 0){
                return true;
            }

            return false;
        },

        shouldDisplayHelpText(){
            return this.callEnabled;

        },

        anyAlternateFieldIsEmpty(){
            return this.alternateNameIsEmpty
                || this.alternateRelationshipIsEmpty
                || this.alternateEmailIsEmpty
                || this.alternatePhoneIsEmpty;
        },

        alternatePhoneIsEmpty(){
            if(this.newNumberIsAlternate){
                return this.newPhoneNumber.length === 0;
            }

            return this.alternateContactDetails.length !== 0
                && this.alternateContactDetails[0].agentTelephone.length !== 0
                && this.alternateContactDetails[0].agentTelephone.number.length === 0;
        },

        alternateEmailIsEmpty(){
            return this.alternateContactDetails.length !== 0
                && this.alternateContactDetails[0].agentEmail.length === 0;
        },

        alternateRelationshipIsEmpty(){
            return this.alternateContactDetails.length !== 0
                && this.alternateContactDetails[0].agentRelationship.length === 0;
        },

        alternateNameIsEmpty(){
            return this.alternateContactDetails.length !== 0
                && this.alternateContactDetails[0].agentName.length === 0;
        },

        alternateSaveBtnIsVisible(){
            if (! this.initialValuesAreUnchanged){
                return true;
            }

            return this.anyAlternateFieldIsEmpty || this.helperTextClicked;
        },

        altPhoneNumberIsValid(){
            return ! this.alternatePhoneIsEmpty
                && this.alternateContactDetails[0].agentTelephone.number.length === 10;
        },

        disableAltSaveButton(){
            if (this.anyAlternateFieldIsEmpty){
                return true;
            }

            if (! this.altPhoneNumberIsValid){
                return true;
            }

            if (! this.isValidEmail){
                return true;
            }

            return this.initialValuesAreUnchanged;
        },

        isValidEmail(){
            const pattern = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/;
            return ! this.alternateEmailIsEmpty
                && pattern.test(this.alternateContactDetails[0].agentEmail);
        },

        alternateEmailHasNotChanged(){
            return this.initialAlternateEmailSavedInDB === this.alternateContactDetails[0].agentEmail;
        },

        alternateNameHasNotChanged(){
            return this.initialAlternateNameSavedInDB === this.alternateContactDetails[0].agentName;
        },

        alternatePhoneHasNotChanged(){
            return this.initialAlternatePhoneSavedInDB === this.alternateContactDetails[0].agentTelephone.number;
        },

        alternateRelationshipHasNotChanged(){
          return this.initialAlternateRelationshipSavedInDB === this.alternateContactDetails[0].agentRelationship;
        },

        initialValuesAreUnchanged(){
            return this.alternateRelationshipHasNotChanged
                && this.alternateEmailHasNotChanged
                && this.alternateNameHasNotChanged
                && this.alternatePhoneHasNotChanged;
        },
    },

    methods:{
        deleteAlternateContact(deleteAlternatePhoneOnly){
            if (! confirm("Are you sure you want to delete alternate phone?")){
                return;
            }
            this.loading = true;
            axios.post('/manage-patients/delete-alternate-contact', {
                patientUserId:this.userId,
                deleteOnlyPhone:deleteAlternatePhoneOnly
            }).then((response => {
                this.getAlternateContactData();
                if (this.callEnabled){
                    EventBus.$emit("refresh:phoneData");
                }
                if (response.data.hasOwnProperty('message')){
                    console.log(response.data.message);
                }
                this.loading = false;
            })).catch((error) => {
                this.loading = false;
                this.responseErrorMessage(error.message);
            });
        },

        saveNewAlternateNumberAndContactDetails(){
            this.loading = true;
            const alternateNewEmail = this.alternateContactDetails[0].agentEmail;
            const alternateNewRelationship = this.alternateContactDetails[0].agentRelationship;
            const alternateNewName = this.alternateContactDetails[0].agentName;
            const alternatePhoneNumber = this.newNumberIsAlternate
                ? this.newPhoneNumber :
                this.alternateContactDetails[0].agentTelephone.number;

            if(alternatePhoneNumber.length === 0){
                alert("Alternate contact phone number is required.");
                this.loading = false;
                return;
            }

            if(alternateNewRelationship.length === 0){
                alert("Alternate contact relationship is required.");
                this.loading = false;
                return;
            }

            if(alternateNewName.length === 0){
                alert("Alternate contact name is required.");
                this.loading = false;
                return;
            }

            if(alternateNewEmail.length === 0){
                alert("Alternate contact email is required.");
                this.loading = false;
                return;
            }

            if (alternateNewEmail.length > 0 && ! this.isValidEmail){
                alert("Alternate email is not a valid email format.");
                this.loading = false;
                return;
            }

            axios.post('/manage-patients/new/alternate/phone', {
                phoneNumber:alternatePhoneNumber,
                patientUserId:this.userId,
                agentName:alternateNewName,
                agentRelationship:alternateNewRelationship,
                agentEmail:alternateNewEmail,
            }).then((response => {
                    this.getAlternateContactData();

                    if (this.callEnabled){
                        EventBus.$emit("refresh:phoneData");
                    }

                    if (response.data.hasOwnProperty('message')){
                        console.log(response.data.message);
                    }

                    this.loading = false;
                })).catch((error) => {
                this.loading = false;
                this.responseErrorMessage(error.response)
            });

        },

        showAlternateFields(){
            if(this.helperTextClicked){
                return this.helperTextClicked = false;
            }

            this.helperTextClicked = true;
        },

        getAlternateContactData(){
            if (!this.callEnabled){
                this.loading = true;

                axios.post('/manage-patients/get-alternate-contact', {
                    patientUserId:this.userId,
                    requestIsFromCallPage:this.callEnabled,
                }).then((response => {
                    if (response.data.agentContactFields.length !== 0){
                        const agentDetails = response.data.agentContactFields[0];
                        this.alternateContactDetails[0].agentEmail = agentDetails.agentEmail;
                        this.alternateContactDetails[0].agentName = agentDetails.agentName;
                        this.alternateContactDetails[0].agentRelationship = agentDetails.agentRelationship;
                        this.alternateContactDetails[0].agentTelephone = agentDetails.agentTelephone;
                        this.initialAlternatePhoneSavedInDB = agentDetails.agentTelephone.number;
                        this.initialAlternateEmailSavedInDB = agentDetails.agentEmail;
                        this.initialAlternateRelationshipSavedInDB = agentDetails.agentRelationship;
                        this.initialAlternateNameSavedInDB = agentDetails.agentName;
                    }
                    this.loading = false;
                })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error);
                });
            }else {
                this.alternateContactDetails[0].agentEmail = this.altContact.agentEmail;
                this.alternateContactDetails[0].agentName = this.altContact.agentName;
                this.alternateContactDetails[0].agentRelationship = this.altContact.agentRelationship;
                this.alternateContactDetails[0].agentTelephone = this.altContact.agentTelephone;
                this.initialAlternatePhoneSavedInDB = this.altContact.agentTelephone.number;
                this.initialAlternateEmailSavedInDB = this.altContact.agentEmail;
                this.initialAlternateRelationshipSavedInDB = this.altContact.agentRelationship;
                this.initialAlternateNameSavedInDB = this.altContact.agentName;
            }
        },

        responseErrorMessage(exception){
            if (exception.status === 422) {
                const e = exception.data;
                alert(e);
            }
        },

    },

    created() {
        this.getAlternateContactData();
    },

    mounted(){

    }


}
</script>

<style scoped>
.borderColor{
    border: #f62056 solid 1px;
}

.alt-phone-number{
    display: inline-flex;
    /*padding-left: 11px;*/
}

.phone-number{
    background-color: transparent;
    max-width: 140px;
    min-width: 140px;
}

.alternative-field{
    background-color: transparent;
    max-width: 270px;
    min-width: 270px;
    margin-bottom: 18px;
    margin-right: 10px;
}

.plus-one{
    padding-right: 26px;
    padding-top: 10px;
    background-color: transparent;
}

.save-alt-contact{
    display: inline;
    height: 29px;
    padding: 5px;
    color: white;
}

.delete-alt-contact{
    display: inline;
    height: 29px;
    padding: 5px;
    color: white;
}

.clear-alt-contact{
    background-color: transparent;
    display: inline;
    height: 30px;
    padding: 5px;
    color: red;
    margin-left: 12px;
}

    .alternate-fields{
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .alt-btns{
        max-width: 310px;
        min-width: 310px;
    }
</style>
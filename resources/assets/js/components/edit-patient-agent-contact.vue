<template>
    <div class="input-group">

        <div>
            <loader v-if="loading"></loader>
        </div>

        <div>
            <button v-if="!loading && shouldDisplayHelpText"
                      class="help-block"
                      title="Missing agent contact details(optional)."
                      style="color: #50b2e2; font-size: 15px; cursor: pointer"
                      @click="showAgentFields">
                {{helperText}}
            </button>
        </div>

        <div v-if="!loading" class="agent-fields">
            <div v-if="enableAgentFields">
                <input name="alternativeContactName"
                       class="form-control alternative-field"
                       maxlength="40"
                       minlength="3"
                       type="text"
                       title="Type agent contact name"
                       placeholder="Agent contact name"
                       v-model="agentContactDetails[0].agentName"
                       :disabled="loading"/>

                <input v-if="shouldDisplayThisAgentField()"
                       name="alternativeEmail"
                       class="form-control alternative-field"
                       type="text"
                       title="Type agent contact email"
                       placeholder="Agent contact email"
                       v-model="agentContactDetails[0].agentEmail"
                       :disabled="loading"/>
                <br>
                <input v-if="shouldDisplayThisAgentField()"
                       name="alternativeRelationship"
                       class="form-control alternative-field"
                       maxlength="20"
                       minlength="3"
                       type="text"
                       title="Type agent contact relationship"
                       placeholder="Agent contact relationship"
                       v-model="agentContactDetails[0].agentRelationship"
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
                           v-model="agentContactDetails[0].agentTelephone.number"
                           :disabled="loading"/>
                </div>

                <div v-if="!loading" class="alt-btns">
                    <br>
                    <button v-if="agentSaveBtnIsVisible"
                            class="btn btn-success btn-sm save-alt-contact"
                            type="button"
                            @click="saveNewAgentNumberAndContactDetails"
                            :disabled="loading || disableAltSaveButton">
                        Save agent contact
                    </button>

                    <button v-if="agentClearBtnIsVisible"
                            class="btn btn-danger btn-sm delete-alt-contact"
                            type="button"
                            @click="deleteAgentContact(false)"
                            :disabled="loading || ! agentClearBtnIsVisible">
                        Delete agent contact
                    </button>
                </div>
            </div>


        </div>
    </div>
</template>

<script>

import axios from "../bootstrap-axios";
import LoaderComponent from "./loader";
import EventBus from '../admin/time-tracker/comps/event-bus';

export default {

    name: "edit-patient-agent-contact",

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
            initialAgentPhoneSavedInDB:'',
            initialAgentEmailSavedInDB:'',
            initialAgentRelationshipSavedInDB:'',
            initialAgentNameSavedInDB:'',
            agentContactDetails:[
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
        enableAgentFields(){
            return ! this.callEnabled || this.helperTextClicked;
        },

        helperText(){
            if(this.agentPhoneIsEmpty && (! this.agentNameIsEmpty
                || ! this.agentRelationshipIsEmpty
                || ! this.agentEmailIsEmpty)){
                return this.helperTextClicked ? 'Hide agent contact'
                    : 'Add missing agent contact phone number';
            }

            if (this.anyAgentFieldIsEmpty){
                return this.helperTextClicked ? 'Hide agent contact'
                    : 'Add missing agent contact details';
            }

            return this.helperTextClicked ? 'Hide agent contact'
                : 'Show agent contact details';


        },

        agentClearBtnIsVisible(){
            if (! this.agentEmailHasNotChanged && this.initialAgentEmailSavedInDB.length !== 0){
                return true;
            }

            if (! this.agentNameHasNotChanged && this.initialAgentNameSavedInDB.length !== 0){
                return true;
            }

            if (! this.agentPhoneHasNotChanged && this.initialAgentPhoneSavedInDB.length !== 0){
                return true;
            }

            if (! this.agentRelationshipHasNotChanged && this.initialAgentRelationshipSavedInDB.length !== 0){
                return true;
            }

            return false;
        },

        shouldDisplayHelpText(){
            return this.callEnabled;

        },

        anyAgentFieldIsEmpty(){
            return this.agentNameIsEmpty
                || this.agentRelationshipIsEmpty
                || this.agentEmailIsEmpty
                || this.agentPhoneIsEmpty;
        },

        agentPhoneIsEmpty(){
            if(this.newNumberIsAgent){
                return this.newPhoneNumber.length === 0;
            }

            return this.agentContactDetails.length !== 0
                && this.agentContactDetails[0].agentTelephone.length !== 0
                && this.agentContactDetails[0].agentTelephone.number.length === 0;
        },

        agentEmailIsEmpty(){
            return this.agentContactDetails.length !== 0
                && this.agentContactDetails[0].agentEmail.length === 0;
        },

        agentRelationshipIsEmpty(){
            return this.agentContactDetails.length !== 0
                && this.agentContactDetails[0].agentRelationship.length === 0;
        },

        agentNameIsEmpty(){
            return this.agentContactDetails.length !== 0
                && this.agentContactDetails[0].agentName.length === 0;
        },

        agentSaveBtnIsVisible(){
            if (! this.initialValuesAreUnchanged){
                return true;
            }

            return this.anyAgentFieldIsEmpty || this.helperTextClicked;
        },

        altPhoneNumberIsValid(){
            return ! this.agentPhoneIsEmpty
                && this.agentContactDetails[0].agentTelephone.number.length === 10;
        },

        disableAltSaveButton(){
            if (this.anyAgentFieldIsEmpty){
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
            return ! this.agentEmailIsEmpty
                && pattern.test(this.agentContactDetails[0].agentEmail);
        },

        agentEmailHasNotChanged(){
            return this.initialAgentEmailSavedInDB === this.agentContactDetails[0].agentEmail;
        },

        agentNameHasNotChanged(){
            return this.initialAgentNameSavedInDB === this.agentContactDetails[0].agentName;
        },

        agentPhoneHasNotChanged(){
            return this.initialAgentPhoneSavedInDB === this.agentContactDetails[0].agentTelephone.number;
        },

        agentRelationshipHasNotChanged(){
          return this.initialAgentRelationshipSavedInDB === this.agentContactDetails[0].agentRelationship;
        },

        initialValuesAreUnchanged(){
            return this.agentRelationshipHasNotChanged
                && this.agentEmailHasNotChanged
                && this.agentNameHasNotChanged
                && this.agentPhoneHasNotChanged;
        },
    },

    methods:{
        shouldDisplayThisAgentField(){
            if(! this.callEnabled){
                return true;
            }

            if(this.callEnabled
            && (this.initialAgentEmailSavedInDB.length === 0
                    || this.initialAgentRelationshipSavedInDB.length === 0)){
                return true;
            }

            return false;
        },


        deleteAgentContact(deleteAgentPhoneOnly){
            if (! confirm("Are you sure you want to delete agent phone?")){
                return;
            }
            axios.post('/manage-patients/delete-agent-contact', {
                patientUserId:this.userId,
                deleteOnlyPhone:deleteAgentPhoneOnly
            }).then((response => {
                this.getAgentContactData();
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

        saveNewAgentNumberAndContactDetails(){
            this.loading = true;
            const agentNewEmail = this.agentContactDetails[0].agentEmail;
            const agentNewRelationship = this.agentContactDetails[0].agentRelationship;
            const agentNewName = this.agentContactDetails[0].agentName;
            const agentPhoneNumber = this.newNumberIsAgent
                ? this.newPhoneNumber :
                this.agentContactDetails[0].agentTelephone.number;

            if(agentPhoneNumber.length === 0){
                alert("Agent contact phone number is required.");
                this.loading = false;
                return;
            }

            if(agentNewRelationship.length === 0){
                alert("Agent contact relationship is required.");
                this.loading = false;
                return;
            }

            if(agentNewName.length === 0){
                alert("Agent contact name is required.");
                this.loading = false;
                return;
            }

            if(agentNewEmail.length === 0){
                alert("Agent contact email is required.");
                this.loading = false;
                return;
            }

            if (agentNewEmail.length > 0 && ! this.isValidEmail){
                alert("Agent email is not a valid email format.");
                this.loading = false;
                return;
            }

            axios.post('/manage-patients/new/agent/phone', {
                phoneNumber:agentPhoneNumber,
                patientUserId:this.userId,
                agentName:agentNewName,
                agentRelationship:agentNewRelationship,
                agentEmail:agentNewEmail,
            }).then((response => {
                    this.getAgentContactData();

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

        showAgentFields(){
            if(this.helperTextClicked){
                return this.helperTextClicked = false;
            }

            this.helperTextClicked = true;
        },

        getAgentContactData(){
            if (!this.callEnabled){
                this.loading = true;

                axios.post('/manage-patients/get-agent-contact', {
                    patientUserId:this.userId,
                    requestIsFromCallPage:this.callEnabled,
                }).then((response => {
                    if (response.data.agentContactFields.length !== 0){
                        const agentDetails = response.data.agentContactFields[0];
                        this.agentContactDetails[0].agentEmail = agentDetails.agentEmail;
                        this.agentContactDetails[0].agentName = agentDetails.agentName;
                        this.agentContactDetails[0].agentRelationship = agentDetails.agentRelationship;
                        this.agentContactDetails[0].agentTelephone = agentDetails.agentTelephone;
                        this.initialAgentPhoneSavedInDB = agentDetails.agentTelephone.number;
                        this.initialAgentEmailSavedInDB = agentDetails.agentEmail;
                        this.initialAgentRelationshipSavedInDB = agentDetails.agentRelationship;
                        this.initialAgentNameSavedInDB = agentDetails.agentName;
                    }
                    this.loading = false;
                })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error);
                });
            }else {
                this.agentContactDetails[0].agentEmail = this.altContact.agentEmail;
                this.agentContactDetails[0].agentName = this.altContact.agentName;
                this.agentContactDetails[0].agentRelationship = this.altContact.agentRelationship;
                this.agentContactDetails[0].agentTelephone = this.altContact.agentTelephone;
                this.initialAgentPhoneSavedInDB = this.altContact.agentTelephone.number;
                this.initialAgentEmailSavedInDB = this.altContact.agentEmail;
                this.initialAgentRelationshipSavedInDB = this.altContact.agentRelationship;
                this.initialAgentNameSavedInDB = this.altContact.agentName;
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
        this.getAgentContactData();
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

    .agent-fields{
        margin-bottom: 15px;
    }

    .alt-btns{
        max-width: 310px;
        min-width: 310px;
    }

    .help-block{
        background: transparent;
        border: solid 1px;
        padding: 5px;
    }
</style>
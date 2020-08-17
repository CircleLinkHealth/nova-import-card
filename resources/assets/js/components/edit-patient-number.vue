<template>
    <div class="phone-numbers">
        <div class="input-group">
            <h5 v-if="!loading && shouldDisplayNumberToCallText" style="padding-left: 4px; color: #50b2e2;">Number<br>to Call</h5>
            <template v-if="true" v-for="(number, index) in patientPhoneNumbers">
                <div class="numbers">
                    <div v-if="callEnabled && number.number !== ''" style="margin-top: 7px;">
                        <input name="isPrimary"
                               class="to-call"
                               @click="selectedNumber(number.number)"
                               type="radio"
                               v-model="selectedNumberToCall"
                               :checked="numberIsPrimary(number)">
                    </div>

                    <div v-if="number.number.length !== 0"
                        @mouseover="enableUpdateButton(index)"
                        style="display: inline-flex;">
                        <div class="types">
                            <input name="type"
                                   class="form-control phone-type"
                                   :class="{'bgColor' : numberIsPrimary(number)}"
                                   type="text"
                                   :value="number.type"
                                   :disabled="true"/>
                        </div>

                        <span class="input-group-addon plus-one">+1</span>
                        <input name="number"
                               class="form-control phone-number"
                               :class="{'bgColor' : numberIsPrimary(number)}"
                               type="tel"
                               :value="number.number"
                               :disabled="true"/>
                    </div>
                </div>
                <i v-if="!loading && number.isPrimary === false && number.number.length !== 0"
                   class="glyphicon glyphicon-trash remove-phone"
                   title="Delete Phone Number"
                   @click="deletePhone(number)"></i>

                <button v-if="showMakePrimary(index, number)"
                        class="btn btn-sm update-primaryNumber"
                        type="button"
                        style="display: inline;"
                        @click="updatePrimaryPhone(number.phoneNumberId)"
                        :disabled="number.isPrimary">
                    Make primary
                </button>
                <br>
            </template>
            <div>
                <loader v-if="loading"></loader>
            </div>

            <!--Extra inputs that are requested by user-->
            <div v-for="(input, index) in newInputs" class="extra-inputs">
                <div style="padding-right: 14px; margin-left: -10px;">
                    <div class="numbers">
                        <div class="types">
                       <select2 id="numberType"
                                class="form-control"
                                style="width: 81px;"
                                v-model="newPhoneType">
                           <option v-for="(phoneType, key) in phoneTypesFiltered"
                                   :key="key"
                                   :value="phoneType">
                               {{phoneType}}
                           </option>
                       </select2>
                   </div>


                <span class="input-group-addon" style="padding-right: 26px; padding-top: 10px;">+1</span>
                <input name="number"
                       class="form-control phone-number"
                       maxlength="10"
                       type="tel"
                       title="10-digit US Phone Number"
                       :placeholder="input.placeholder"
                       v-model="newPhoneNumber"
                       :disabled="loading"/>

                <i v-if="!loading"
                   class="glyphicon glyphicon-minus remove-input"
                   title="Remove extra field"
                   @click="removeInputField(index)"></i>


                        <button v-if="addNewFieldClicked"
                                class="btn btn-sm save-number"
                                style="display: inline;"
                                type="button"
                                @click="addNewNumber"
                                :disabled="disableSaveButton">
                            {{setSaveBtnText}}
                        </button>

                       <div v-if="! newNumberIsAlternate && newPhoneNumber.length !== 0" style="display: flex;">
                           <input id="makePrimary"
                                  class="make-primary"
                                  v-model="makeNewNumberPrimary"
                                  type="checkbox">
                           <label for="makePrimary" style="padding-left: 30px;">Make Primary</label>
                       </div>
                </div>
                </div>
            </div>

            <a v-if="allowAddingNewNumber"
               class="glyphicon glyphicon-plus-sign add-new-number"
               title="Add Phone Number"
               @click="addPhoneField()">
                Add phone number
            </a>

            <span v-if="!loading && shouldDisplayHelpText"
                  class="help-block"
                  title="Missing alternate contact details(optional)."
                  style="color: #50b2e2; font-size: 15px; cursor: pointer"
                  @click="showAlternateFields">
                {{helperText}}
            </span>

            <div v-if="!loading && shouldDisplayAlternateFields"
                 class="alternate-fields">
               <input name="alternativeContactName"
                      class="form-control alternative-field"
                      :class="{borderColor : alternateContactDetails[0].agentName.length === 0}"
                      maxlength="40"
                      minlength="3"
                      type="text"
                      title="Type alternate contact name"
                      placeholder="Alternate contact name"
                      v-model="alternateContactDetails[0].agentName"
                      :disabled="loading"/>

               <input name="alternativeEmail"
                      style="margin-left: 10px;"
                      class="form-control alternative-field"
                      :class="{borderColor : alternateContactDetails[0].agentEmail.length === 0}"
                      type="text"
                      title="Type alternate contact email"
                      placeholder="Alternate contact email"
                      v-model="alternateContactDetails[0].agentEmail"
                      :disabled="loading"/>
               <br>
               <input name="alternativeRelationship"
                      class="form-control alternative-field"
                      :class="{borderColor : alternateContactDetails[0].agentRelationship.length === 0}"
                      maxlength="20"
                      minlength="3"
                      type="text"
                      title="Type alternate contact relationship"
                      placeholder="Alternate contact relationship"
                      v-model="alternateContactDetails[0].agentRelationship"
                      :disabled="loading"/>

               <div v-if="! callEnabled || helperTextClicked"
                    class="alt-phone-number">
                   <span class="input-group-addon plus-one"
                         :class="{borderColor : alternateContactDetails[0].agentTelephone.number.length === 0}">
                       +1
                   </span>
                   <input name="number"
                          class="form-control phone-number"
                          :class="{borderColor : alternateContactDetails[0].agentTelephone.number.length === 0}"
                          type="tel"
                          maxlength="10"
                          placeholder="5417543120"
                          v-model="alternateContactDetails[0].agentTelephone.number"
                          :disabled="loading"/>
               </div>

               <div v-if="!loading"
                    class="alt-save-btn">
                   <br>
                   <button v-if="alternateSaveBtnIsVisible"
                           class="btn btn-sm save-alt-contact"
                           type="button"
                           @click="saveNewAlternateNumberAndContactDetails"
                           :disabled="loading || disableAltSaveButton">
                       {{altSaveBtnText}}
                   </button>

                   <button v-if="alternateClearBtnIsVisible"
                            class="btn btn-sm clear-alt-contact"
                            type="button"
                            @click="deleteAlternateContact(false)"
                            :disabled="loading">
                        Delete alternate contact details
                    </button>
               </div>

           </div>

        </div>
    </div>
</template>

<script>
    import LoaderComponent from "./loader";
    import axios from "../bootstrap-axios";
    import EventBus from '../admin/time-tracker/comps/event-bus'
    import CallNumber from "./call-number";

    const alternate = 'alternate';

    export default {
        name: "edit-patient-number",

        components: {
            'loader': LoaderComponent,
            'call-number':CallNumber,
        },

        props: [
            'userId',
            'callEnabled',
        ],

        data(){
            return {
                loading:false,
                patientPhoneNumbers:[],
                newPhoneType:'',
                newPhoneNumber:'',
                newInputs:[],
                phoneTypes:[],
                markPrimaryEnabledForIndex:'',
                makeNewNumberPrimary:false,
                primaryNumber:'',
                initialAlternatePhoneSavedInDB:'',
                initialAlternateEmailSavedInDB:'',
                initialAlternateRelationshipSavedInDB:'',
                initialAlternateNameSavedInDB:'',
                selectedNumberToCall:'',
                phoneTypesFiltered:[],
                helperTextClicked:false,
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
            alternateClearBtnIsVisible(){
                return ! this.callEnabled
                    && ! this.initialValueIsUnchanged
                    && this.alternateSaveBtnIsVisible;
            },
            altSaveBtnText(){
                return this.initialValueIsUnchanged
                    ? 'Save alternate contact'
                    : 'Edit alternate contact'

            },

            shouldDisplayHelpText(){
                if (! this.callEnabled){
                    return false;
                }
                return this.anyAlternateFieldIsEmpty;
            },

            shouldDisplayNumberToCallText(){
                return this.callEnabled && ! this.emptyPatientPhones;
            },

            helperText(){
                if(this.alternatePhoneIsEmpty && (! this.alternateNameIsEmpty
                    || ! this.alternateRelationshipIsEmpty
                    || ! this.alternateEmailIsEmpty)){
                    return this.helperTextClicked ? 'Hide alternate contact'
                        : 'Add missing alternate contact phone number';
                }
                return this.helperTextClicked ? 'Hide alternate contact'
                    : 'Add missing alternate contact details';
            },

            anyAlternateFieldIsEmpty(){
                return this.alternateNameIsEmpty
                || this.alternateRelationshipIsEmpty
                || this.alternateEmailIsEmpty
                || this.alternatePhoneIsEmpty;
            },

            allowAddingNewNumber(){
                const existingNumbers = this.patientPhoneNumbers.filter(number=>number.number.length !== 0);
                return !this.loading && this.newInputs.length === 0
                    && existingNumbers.length < this.phoneTypes.length;
            },

            isValidEmail(){
                return this.alternateContactDetails[0].agentEmail.length !== 0
                    && this.alternateContactDetails[0].agentEmail.includes("@")
                    && (this.alternateContactDetails[0].agentEmail.includes(".com"));
            },

            disableSaveButton(){
                return this.loading
                    || this.newPhoneType.length === 0
                    || isNaN(this.newPhoneNumber.toString())
                    || this.newPhoneNumber.toString().length !== 10;

            },

            emptyPatientPhones(){
                return this.patientPhoneNumbers.length === 0;
            },

            altPhoneNumberIsValid(){
                return ! this.alternatePhoneIsEmpty
                    && this.alternateContactDetails[0].agentTelephone.number.length === 10;
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

            setSaveBtnText(){
                if(this.makeNewNumberPrimary || this.emptyPatientPhones){
                    return'Save & Make Primary';
                }

                if (this.newNumberIsAlternate){
                    if (this.anyAlternateFieldIsEmpty){
                        return "Save alternate contact details";
                    }
                    return "Save alternate number";
                }

                return "Add Number";
            },

            newNumberIsAlternate(){
                return this.newPhoneType.toLowerCase() === alternate;
            },

            addNewFieldClicked(){
                return this.newInputs.length > 0;
            },

            alternateSaveBtnIsVisible(){
                if (! this.initialValueIsUnchanged){
                    return true;
                }

                return this.anyAlternateFieldIsEmpty || this.helperTextClicked;
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
                
                return this.initialValueIsUnchanged;
            },

            initialValueIsUnchanged(){
                return this.initialAlternateRelationshipSavedInDB === this.alternateContactDetails[0].agentRelationship
                && this.initialAlternateEmailSavedInDB === this.alternateContactDetails[0].agentEmail
                && this.initialAlternateNameSavedInDB === this.alternateContactDetails[0].agentName
                && this.initialAlternatePhoneSavedInDB === this.alternateContactDetails[0].agentTelephone.number;
            },

            shouldDisplayAlternateFields(){
                if (! this.callEnabled){
                    return true;
                }

                return this.newNumberIsAlternate || this.helperTextClicked;
            },
        },

        methods: {
            deleteAlternateContact(deleteAlternatePhoneOnly){
                confirm("Are you sure you want to delete alternate contact?");
                this.loading = true;
                axios.post('/manage-patients/delete-alternate-contact', {
                    patientUserId:this.userId,
                    deleteOnlyPhone:deleteAlternatePhoneOnly
                }).then((response => {
                    if (response.data.hasOwnProperty('message')){
                        alert(response.data.message);
                    }
                    this.getPhonesAndContactDetails();
                    this.loading = false;
                })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            },

            showAlternateFields(){
                this.removeInputField();
                if(this.helperTextClicked){
                    return this.helperTextClicked = false;
                }

                this.helperTextClicked = true;
            },

            filterOutSavedPhoneTypes(){
                let existingPhoneNumbersTypes = [];
                let phoneTypesFiltered = [];
                Object.keys(this.patientPhoneNumbers).forEach(numberKey => {
                    existingPhoneNumbersTypes.push(this.patientPhoneNumbers[numberKey].type);
                });

                Object.keys(this.phoneTypes).forEach(typeKey => {
                    const numberType = this.phoneTypes[typeKey];
                    if(! existingPhoneNumbersTypes.includes(numberType)){
                        phoneTypesFiltered.push(numberType);
                    }
                });

                this.phoneTypesFiltered = [];
                this.phoneTypesFiltered.push(...phoneTypesFiltered);
            },

            showMakePrimary(index, number){
                return this.isIndexToUpdate(index)
                    && number.isPrimary === false
                    && number.type.toLowerCase() !== alternate;
            },

            emitPrimaryNumber(){
                const primaryNumber =  this.patientPhoneNumbers.filter(n=>n.isPrimary).map(function (phone) {
                    return phone.number;
                });

                if(primaryNumber.length !== 0){
                    this.primaryNumber =  primaryNumber[0];
                    EventBus.$emit("selectedNumber:toCall", this.primaryNumber);
                }

            },

            selectedNumber(number){
               EventBus.$emit("selectedNumber:toCall", number);
            },

            numberIsPrimary(number){
                return number.isPrimary;
            },

            updatePrimaryPhone(phoneNumberId){
                confirm("Are you sure you want to mark this number as primary number");
                this.loading = true;
                axios.post('/manage-patients/mark/primary-phone', {
                    phoneId:phoneNumberId,
                    patientUserId:this.userId,
                }).then((response => {
                        this.getPhonesAndContactDetails();
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response);
                });
            },

            isIndexToUpdate(index){
            return index === this.markPrimaryEnabledForIndex;
            },

            enableUpdateButton(index){
                this.markPrimaryEnabledForIndex = index;
            },

            resetData(){
                this.patientPhoneNumbers = [];
                this.phoneTypes = [];
                this.newInputs = [];
                this.makeNewNumberPrimary = false;
                this.helperTextClicked = false;
            },

            getPhonesAndContactDetails(){
                this.loading = true;
                this.resetData();
                axios.post('/manage-patients/get-phones', {
                    patientUserId:this.userId,
                    requestIsFromCallPage:this.callEnabled,
                })
                    .then((response => {
                        this.patientPhoneNumbers.push(...response.data.phoneNumbers);
                        this.phoneTypes.push(...response.data.phoneTypes);

                        if(response.data.hasOwnProperty('agentContactFields') && response.data.agentContactFields.length !== 0) {
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
                        this.emitPrimaryNumber();
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response);
                });
            },

            addPhoneField(){
                if (this.newInputs.length > 0) {
                    alert('Please save the existing field first');
                    return;
                }
                this.newPhoneNumber = '';
                this.newPhoneType = '';
                this.filterOutSavedPhoneTypes();

                const arr = {
                  placeholder: '5417543120'
                };

                this.newInputs.push(arr);
            },

            addNewNumber(){
                if (this.newNumberIsAlternate){
                    this.saveNewAlternateNumberAndContactDetails();
                }else{
                    this.saveNewNumber();
                }

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
                })
                    .then((response => {
                        this.getPhonesAndContactDetails();
                        if (response.data.hasOwnProperty('message')){
                            alert(response.data.message);
                        }
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response)
                });

            },

            responseErrorMessage(exception){
                if (exception.status === 422) {
                    const e = exception.data;
                    alert(e);
                }
            },

            saveNewNumber(){
                this.loading = true;
                if (this.newPhoneType.length === 0){
                    alert("Please choose phone number type");
                    this.loading = false;
                    return;
                }

                if (this.newPhoneNumber.length === 0){
                    alert("Phone number is required.");
                    this.loading = false;
                    return;
                }

                if (this.patientPhoneNumbers.length === 0){
                    this.makeNewNumberPrimary = true;
                }

                axios.post('/manage-patients/new/phone', {
                    phoneType:this.newPhoneType,
                    phoneNumber:this.newPhoneNumber,
                    patientUserId:this.userId,
                    makePrimary:this.makeNewNumberPrimary,
                })
                    .then((response => {
                        this.getPhonesAndContactDetails();
                        if (response.data.hasOwnProperty('message')){
                            alert(response.data.message);
                        }
                        this.loading = false;
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response)
                });
            },

            removeInputField(index){
                this.loading = true;
                this.newPhoneType = '';
                this.newPhoneNumber = '';
                this.newInputs = [];
                this.newInputs.splice(index, 1);
                this.loading = false;

            },

            deletePhone(number){
                if (number.type.toLowerCase() === alternate){
                    this.deleteAlternateContact(true);
                }

                confirm("Are you sure you want to delete this phone number");
                this.loading = true;
                const phoneNumberId = number.hasOwnProperty('phoneNumberId')
                ? number.phoneNumberId
                : '';

                axios.post('/manage-patients/delete-phone', {
                    phoneId:phoneNumberId,
                    patientUserId:this.userId,
                })
                    .then((response => {
                        this.getPhonesAndContactDetails();
                        this.loading = false;
                        if (response.data.hasOwnProperty('message')){
                            alert(response.data.message);
                        }
                    })).catch((error) => {
                    this.loading = false;
                    console.log(error.message);
                });
            }
        },

        created() {
            this.getPhonesAndContactDetails();
        }
    }
</script>

<style scoped>
.phone-numbers{
    float: left;
}

.extra-inputs{
    display: inline-flex;
    padding-bottom: 10px;
    padding-left: 10px;
    white-space: nowrap;
}
    .phone-type{
        min-width: 80px;
        max-width: 80px;
        text-align: center;
        background-color: transparent;
    }

.remove-phone{
        top: -7px;
        padding-left: 3px;
        color: red;
        cursor: pointer;
    }
    .remove-input{
        margin-left: 19px;
        padding-top: 5px;
        color: red;
        cursor: pointer;
        background-color: transparent;
    }
    .stop-edit-phone{
        margin-left: 10px;
        padding-top: 5px;
        color: #50b2e2;
        cursor: pointer;
    }

    .save-number{
        margin-left: 15px;
        height: 29px;
        padding: 5px;
        color: #50b2e2;
    }

    .save-alt-contact{
        display: inline;
        height: 29px;
        padding: 5px;
        color: #50b2e2;
    }

    .clear-alt-contact{
        background-color: transparent;
        display: inline;
        height: 30px;
        padding: 5px;
        color: red;
        margin-left: 12px;
    }



    .add-new-number{
        word-spacing: -10px;
        color: #50b2e2;
        font-size: 20px;
        cursor: pointer;
        padding: 10px;
        margin-bottom: 15px;
    }
    .plus-one{
        padding-right: 26px;
        padding-top: 10px;
        background-color: transparent;
    }
    .numbers{
        display: inline-flex;
        padding-bottom: 10px;
    }

    .make-primary{
        display: flex;
        margin-left: 15px;
    }

    .to-call{
        display: flex;
        margin-right: 10px;
    }
    .types{
        padding-right: 6px;
        /*padding-left: 10px;*/
    }
    .phone-number{
        background-color: transparent;
        max-width: 140px;
        min-width: 140px;
    }

    .alt-phone-number{
        display: inline-flex;
        padding-left: 11px;
    }

    .alternative-field{
        background-color: transparent;
        max-width: 270px;
        min-width: 270px;
        margin-bottom: 18px;
    }
    .update-primaryNumber{
        height: 29px;
        padding: 5px;
        color: #50b2e2;
        margin-left: 10px;
        margin-top: -20px
    }

.bgColor{
    background-color:  #c4ebff;
}

.borderColor{
    border: #f62056 solid 1px;
}
.alternate-fields{
    margin-top: 15px;
    margin-bottom: 15px;
}
.alt-save-btn{

}
</style>
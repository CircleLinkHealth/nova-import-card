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
                       <v-select id="numberType"
                                 v-model="newPhoneType"
                                 :options="phoneTypesFiltered">
                       </v-select>
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
            <div v-if="shouldShowAlternateContactComponent">
                <edit-patient-alternate-contact ref="editPatientAlternateContact"
                                                :user-id="userId"
                                                :call-enabled="callEnabled"
                                                :alt-contact="alternateContactDetails[0]">
                </edit-patient-alternate-contact>
            </div>
        </div>
    </div>

</template>


<script>
    import LoaderComponent from "./loader";
    import axios from "../bootstrap-axios";
    import EventBus from '../admin/time-tracker/comps/event-bus'
    import CallNumber from "./call-number";
    import EditPatientAlternateContact from "./edit-patient-alternate-contact";
    import VueSelect from "vue-select";

    const alternate = 'alternate';

    export default {
        name: "edit-patient-number",

        components: {
            'loader': LoaderComponent,
            'call-number':CallNumber,
            'edit-patient-alternate-contact':EditPatientAlternateContact,
            'v-select': VueSelect
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
                selectedNumberToCall:'',
                phoneTypesFiltered:[],
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
            shouldShowAlternateContactComponent(){
                return this.alternateNumberIsSet || (!this.loading && this.callEnabled);
            },

            shouldDisplayAlternateDetailsText(){
                 return this.callEnabled
                     && this.alternateContactDetails[0].agentRelationship.length !==0
                     && this.alternateContactDetails[0].agentName.length !==0;
            },

            shouldDisplayNumberToCallText(){
                return this.callEnabled && ! this.emptyPatientPhones;
            },

            allowAddingNewNumber(){
                const existingNumbers = this.patientPhoneNumbers.filter(number=>number.number.length !== 0
                && number.type !== 'Alternate');

                return !this.loading && this.newInputs.length === 0
                    && existingNumbers.length < this.phoneTypes.length;
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

            setSaveBtnText(){
                if(this.makeNewNumberPrimary || this.emptyPatientPhones){
                    return'Save & Make Primary';
                }

                if (this.newNumberIsAlternate){
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

            alternateNumberIsSet(){
                return this.patientPhoneNumbers.filter(number=>number.number.length !== 0
                    && number.type.toLowerCase() === alternate).length !== 0;
            },
        },

        methods: {
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
                        this.getPatientPhoneNumbers();
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
            },

            getPatientPhoneNumbers(){
                this.loading = true;
                this.resetData();
                axios.post('/manage-patients/get-phones', {
                    patientUserId:this.userId,
                    requestIsFromCallPage:this.callEnabled,
                })
                    .then((response => {
                        this.patientPhoneNumbers.push(...response.data.phoneNumbers);
                        this.phoneTypes.push(...response.data.phoneTypes);
                        if (response.data.agentContactFields.length !== 0){
                            const agentDetails = response.data.agentContactFields;
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

            responseErrorMessage(exception){
                if (exception.status === 422) {
                    const e = exception.data;
                    alert(e);
                }

                console.log(e);
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
                        this.getPatientPhoneNumbers();
                        if (response.data.hasOwnProperty('message')){
                            console.log(response.data.message);
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
                     this.$refs.editPatientAlternateContact.deleteAlternateContact(true);
                     return;
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
                        this.getPatientPhoneNumbers();
                        this.loading = false;
                        if (response.data.hasOwnProperty('message')){
                            console.log(response.data.message);
                        }
                    })).catch((error) => {
                    this.loading = false;
                    this.responseErrorMessage(error.response);
                });
            },

            // setAlternateContactRelationship(){
            //     const alternateContact = this.$refs.editPatientAlternateContact.alternateContactDetails;
            //     if (alternateContact === undefined){
            //         return '';
            //     }
            //     return this.$refs.editPatientAlternateContact.alternateContactDetails[0].agentRelationship;
            // },
        },

        created() {
            this.getPatientPhoneNumbers();
        },

        mounted(){
            const self = this;
            EventBus.$on("refresh:phoneData", function () {
                self.getPatientPhoneNumbers();
            });
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

   .save-number{
        margin-left: 15px;
        height: 29px;
        padding: 5px;
        color: #50b2e2;
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

    .alt-contact-block{
        margin-top: 30px;
        margin-bottom: -15px;
    }

</style>